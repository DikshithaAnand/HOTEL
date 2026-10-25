<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HotelSetting;
use App\Models\Payment;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Display the hotel management dashboard.
     */
    public function index()
    {
        $hotel = Hotel::where('slug', 'temairazu-hotel')->first();
        $hotelId = $hotel?->id;

        $bookings = Booking::query();
        $rooms = Room::query();
        $guests = Guest::query();

        if ($hotelId) {
            $bookings->where('hotel_id', $hotelId);
            $rooms->where('hotel_id', $hotelId);

            $guests->whereHas('bookings', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            });
        }

        $totalBookings = (clone $bookings)->count();
        $totalRooms = (clone $rooms)->count();

        $availableRooms = (clone $rooms)
            ->where('status', 'available')
            ->count();

        $occupiedRooms = (clone $rooms)
            ->where('status', 'occupied')
            ->count();

        $reservedRooms = (clone $rooms)
            ->where('status', 'reserved')
            ->count();

        $otherRooms = max(
            0,
            $totalRooms - $availableRooms - $occupiedRooms - $reservedRooms
        );

        $totalGuests = (clone $guests)->count();

        $monthStart = Carbon::now()->startOfMonth();
        $nextMonthStart = Carbon::now()->addMonth()->startOfMonth();

        $revenueQuery = Payment::query()
            ->where('status', 'completed')
            ->whereHas('booking', function ($query) use (
                $hotelId,
                $monthStart,
                $nextMonthStart
            ) {
                if ($hotelId) {
                    $query->where('hotel_id', $hotelId);
                }

                $query->where('created_at', '>=', $monthStart)
                    ->where('created_at', '<', $nextMonthStart);
            });

        $revenueThisMonth = (float) $revenueQuery->sum('amount');

        $activeBookings = (clone $bookings)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->count();

        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100)
            : 0;

        $recentBookings = (clone $bookings)
            ->with(['guest', 'rooms.roomType'])
            ->latest()
            ->take(5)
            ->get();

        $allBookings = (clone $bookings)
            ->with(['guest', 'rooms.roomType', 'invoice', 'payments'])
            ->latest()
            ->get();

        $allGuests = (clone $guests)
            ->withCount('bookings')
            ->latest()
            ->get();

        $allRooms = (clone $rooms)
            ->with('roomType')
            ->orderBy('room_number')
            ->get();

        $hotelSettings = $hotelId
            ? HotelSetting::where('hotel_id', $hotelId)->first()
            : null;

        return view('welcome', compact(
            'totalBookings',
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'reservedRooms',
            'otherRooms',
            'totalGuests',
            'revenueThisMonth',
            'activeBookings',
            'occupancyRate',
            'recentBookings',
            'allBookings',
            'allGuests',
            'allRooms',
            'hotel',
            'hotelSettings'
        ));
    }

    /**
     * Create a booking using the existing booking service.
     */
    public function store(
        Request $request,
        BookingService $bookingService
    ) {
        $hotel = Hotel::where('slug', 'temairazu-hotel')->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],

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

            'room_id' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')
                    ->where('hotel_id', $hotel->id),
            ],

            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $booking = $bookingService->create($hotel, [
                'guest' => [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ],

                'rooms' => [(int) $validated['room_id']],

                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],

                'adults' => (int) $validated['adults'],
                'children' => (int) ($validated['children'] ?? 0),

                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'booking' => 'The booking could not be saved. Please check the room availability and try again.',
                ]);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with(
                'success',
                'Booking ' . $booking->booking_reference . ' created successfully.'
            );
    }
}
