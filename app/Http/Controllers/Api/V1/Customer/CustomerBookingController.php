<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Constants\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\Booking\BookingService;
use App\Services\Room\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerBookingController extends Controller
{
    private function customerGuest(Request $request): Guest
    {
        $customer = $request->user();

        abort_unless(
            $customer
                && $customer->tenant_id === null
                && ! $customer->isSuperAdmin(),
            403,
            'Customer account required.'
        );

        return Guest::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone ?: '',
            ]
        );
    }

    public function index(Request $request): JsonResponse
    {
        $guest = $this->customerGuest($request);

        $bookings = Booking::where('guest_id', $guest->id)
            ->with(['hotel', 'rooms.roomType', 'invoice'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function store(
        Request $request,
        BookingService $bookingService,
        AvailabilityService $availabilityService
    ): JsonResponse {
        $guest = $this->customerGuest($request);

        $data = $request->validate([
            'check_in_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'check_out_date' => [
                'required',
                'date',
                'after:check_in_date',
            ],
            'room_type_id' => [
                'required',
                'integer',
                'exists:room_types,id',
            ],
            'adults' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'children' => [
                'sometimes',
                'integer',
                'min:0',
                'max:10',
            ],
            'special_requests' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        // Find the active hotel using its actual database slug.
        $hotel = Hotel::where('slug', 'temairazu-hotel')
            ->where('is_active', true)
            ->firstOrFail();

        $start = Carbon::parse($data['check_in_date'])->startOfDay();
        $end = Carbon::parse($data['check_out_date'])->startOfDay();
        $roomTypeId = (int) $data['room_type_id'];

        // Check availability for every night of the requested stay.
        $availability = $availabilityService->getAvailabilityMatrix(
            $hotel,
            $start,
            $end,
            $roomTypeId
        );

        $days = collect($availability['matrix'] ?? []);
        $expectedNights = (int) $start->diffInDays($end);

        $available = $expectedNights > 0
            && $days->count() === $expectedNights;

        if ($available) {
            foreach ($days as $day) {
                $roomType = collect($day['room_types'] ?? [])
                    ->first(
                        fn ($type) =>
                            (int) ($type['room_type_id'] ?? 0)
                            === $roomTypeId
                    );

                if (
                    ! $roomType
                    || (int) ($roomType['available_rooms'] ?? 0) < 1
                ) {
                    $available = false;
                    break;
                }
            }
        }

        if (! $available) {
            throw ValidationException::withMessages([
                'room_type_id' => [
                    'This room type is not available for every night of your selected stay.',
                ],
            ]);
        }

        // Select a room without an overlapping active booking.
        $room = Room::where('hotel_id', $hotel->id)
            ->where('room_type_id', $roomTypeId)
            ->whereDoesntHave('bookings', function ($query) use ($start, $end) {
                $query
                    ->whereNotIn('status', [
                        BookingStatus::CANCELLED,
                        BookingStatus::NO_SHOW,
                    ])
                    ->where('check_in_date', '<', $end->toDateString())
                    ->where('check_out_date', '>', $start->toDateString());
            })
            ->first();

        if (! $room) {
            throw ValidationException::withMessages([
                'room_type_id' => [
                    'No room is available for those dates. Please search again.',
                ],
            ]);
        }

        // Create the booking through the existing BookingService.
        $booking = DB::transaction(function () use (
            $bookingService,
            $hotel,
            $data,
            $guest,
            $room
        ) {
            return $bookingService->create($hotel, [
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'rooms' => [$room->id],
                'guest_id' => $guest->id,
                'adults' => (int) $data['adults'],
                'children' => (int) ($data['children'] ?? 0),
                'notes' => $data['special_requests'] ?? null,
                'status' => BookingStatus::PENDING,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Your booking has been created.',
            'data' => $booking->load([
                'hotel',
                'rooms.roomType',
                'invoice',
            ]),
        ], 201);
    }
}
