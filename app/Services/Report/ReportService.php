<?php

namespace App\Services\Report;

use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Return operational dashboard statistics for one hotel.
     *
     * Results are cached for five minutes to reduce repeated database
     * queries when the dashboard is refreshed.
     */
    public function getDashboardStats(Hotel $hotel): array
    {
        $cacheKey = "hotel:{$hotel->id}:dashboard_stats";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($hotel): array {
                $roomIds = $hotel->rooms()->pluck('id')->toArray();
                $totalRooms = count($roomIds);

                $occupiedCount = $hotel->rooms()
                    ->where('status', 'occupied')
                    ->count();

                $occupancyRate = $totalRooms > 0
                    ? round(($occupiedCount / $totalRooms) * 100, 2)
                    : 0.00;

                $roomStatuses = $hotel->rooms()
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $housekeepingStats = HousekeepingTask::whereIn('room_id', $roomIds)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $activeMaintenance = MaintenanceRequest::whereIn('room_id', $roomIds)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->count();

                $activeBookingsCount = $hotel->bookings()
                    ->where('status', 'checked_in')
                    ->count();

                $totalBookings = $hotel->bookings()->count();

                $bookingStatuses = $hotel->bookings()
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $guestCount = $hotel->bookings()
                    ->whereNotNull('guest_id')
                    ->distinct('guest_id')
                    ->count('guest_id');

                $recentBookings = $hotel->bookings()
                    ->with(['guest', 'rooms.roomType'])
                    ->latest('created_at')
                    ->limit(8)
                    ->get()
                    ->map(function ($booking) {
                        return [
                            'id' => $booking->id,
                            'booking_reference' => $booking->booking_reference,
                            'guest_name' => $booking->guest
                                ? trim(
                                    $booking->guest->first_name . ' '
                                    . $booking->guest->last_name
                                )
                                : 'Guest',
                            'guest_email' => $booking->guest?->email,
                            'check_in_date' => $booking->check_in_date?->toDateString(),
                            'check_out_date' => $booking->check_out_date?->toDateString(),
                            'status' => $booking->status,
                            'total_amount' => (float) $booking->total_amount,
                            'rooms' => $booking->rooms->map(function ($room) {
                                return [
                                    'id' => $room->id,
                                    'number' => $room->room_number
                                        ?? $room->name
                                        ?? (string) $room->id,
                                    'room_type' => $room->roomType?->name,
                                ];
                            })->values()->all(),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'hotel' => [
                        'id' => $hotel->id,
                        'name' => $hotel->name,
                        'currency' => $hotel->settings?->currency ?? 'JPY',
                        'timezone' => $hotel->settings?->timezone ?? 'Asia/Tokyo',
                    ],
                    'total_rooms' => $totalRooms,
                    'occupied_rooms' => $occupiedCount,
                    'occupancy_rate' => $occupancyRate,
                    'total_bookings' => $totalBookings,
                    'guest_count' => $guestCount,
                    'booking_statuses' => $bookingStatuses,
                    'room_statuses' => [
                        'available' => $roomStatuses['available'] ?? 0,
                        'occupied' => $roomStatuses['occupied'] ?? 0,
                        'cleaning' => $roomStatuses['cleaning'] ?? 0,
                        'maintenance' => $roomStatuses['maintenance'] ?? 0,
                    ],
                    'housekeeping_tasks' => [
                        'pending' => $housekeepingStats['pending'] ?? 0,
                        'in_progress' => $housekeepingStats['in_progress'] ?? 0,
                        'completed' => $housekeepingStats['completed'] ?? 0,
                    ],
                    'active_maintenance_requests' => $activeMaintenance,
                    'active_bookings_count' => $activeBookingsCount,
                    'recent_bookings' => $recentBookings,
                ];
            }
        );
    }

    /**
     * Compute date-range financial performance KPIs.
     */
    public function getRevenueStats(
        Hotel $hotel,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $startDate = $startDate
            ? Carbon::parse($startDate)->startOfDay()->toDateTimeString()
            : now()->subDays(30)->startOfDay()->toDateTimeString();

        $endDate = $endDate
            ? Carbon::parse($endDate)->endOfDay()->toDateTimeString()
            : now()->endOfDay()->toDateTimeString();

        $bookingIds = $hotel->bookings()->pluck('id')->toArray();

        $paymentsCollected = Payment::whereIn('booking_id', $bookingIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $totalInvoiced = Invoice::whereIn('booking_id', $bookingIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalNightsSold = 0;
        $totalRoomRevenue = 0.00;

        $bookings = $hotel->bookings()
            ->where('check_in_date', '<=', $endDate)
            ->where('check_out_date', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->with('rooms')
            ->get();

        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            foreach ($booking->rooms as $room) {
                $totalNightsSold += $nights;

                $totalRoomRevenue +=
                    (float) ($room->pivot->price_per_night ?? 0.00) * $nights;
            }
        }

        $adr = $totalNightsSold > 0
            ? round($totalRoomRevenue / $totalNightsSold, 2)
            : 0.00;

        $totalRooms = $hotel->rooms()->count();

        $daysInPeriod = max(
            1,
            Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate))
        );

        $totalAvailableNights = $totalRooms * $daysInPeriod;

        $revpar = $totalAvailableNights > 0
            ? round($totalRoomRevenue / $totalAvailableNights, 2)
            : 0.00;

        $methodCounts = Payment::whereIn('booking_id', $bookingIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        return [
            'period' => [
                'start_date' => Carbon::parse($startDate)->toDateString(),
                'end_date' => Carbon::parse($endDate)->toDateString(),
                'days' => $daysInPeriod,
            ],
            'payments_collected' => round((float) $paymentsCollected, 2),
            'total_invoiced' => round((float) $totalInvoiced, 2),
            'room_revenue' => round($totalRoomRevenue, 2),
            'nights_sold' => $totalNightsSold,
            'adr' => $adr,
            'revpar' => $revpar,
            'payment_methods' => [
                'cash' => round((float) ($methodCounts['cash'] ?? 0.00), 2),
                'card' => round((float) ($methodCounts['card'] ?? 0.00), 2),
                'mpesa' => round((float) ($methodCounts['mpesa'] ?? 0.00), 2),
                'bank_transfer' => round(
                    (float) ($methodCounts['bank_transfer'] ?? 0.00),
                    2
                ),
            ],
        ];
    }
}