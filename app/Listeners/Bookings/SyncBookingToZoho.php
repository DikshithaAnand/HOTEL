<?php

namespace App\Listeners\Bookings;

use App\Events\Bookings\BookingCancelled;
use App\Events\Bookings\BookingCheckedIn;
use App\Events\Bookings\BookingCheckedOut;
use App\Events\Bookings\BookingCreated;
use App\Events\Bookings\BookingUpdated;
use App\Services\Cloud\ZohoBookingSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncBookingToZoho implements ShouldHandleEventsAfterCommit
{
    /**
     * Synchronize booking changes after the database transaction commits.
     */
    public function handle(
        BookingCreated|BookingUpdated|BookingCancelled|BookingCheckedIn|BookingCheckedOut $event
    ): void {
        $action = match (true) {
            $event instanceof BookingCreated => 'created',
            $event instanceof BookingCancelled => 'cancelled',
            $event instanceof BookingCheckedIn => 'checked_in',
            $event instanceof BookingCheckedOut => 'checked_out',
            default => 'updated',
        };

        try {
            $success = app(ZohoBookingSyncService::class)->sync(
                $event->booking,
                $action
            );

            if (! $success) {
                Log::warning('Booking was not synchronized with Zoho Catalyst.', [
                    'booking_id' => $event->booking->id,
                    'action' => $action,
                ]);
            }
        } catch (Throwable $exception) {
            // Cloud failures must not undo a locally saved booking.
            Log::error('Booking cloud synchronization listener failed.', [
                'booking_id' => $event->booking->id,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}