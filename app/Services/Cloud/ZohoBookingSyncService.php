<?php

namespace App\Services\Cloud;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ZohoBookingSyncService
{
    /**
     * Send a booking event to Zoho Catalyst.
     *
     * Supported actions:
     * created, updated, cancelled, checked_in, checked_out
     */
    public function sync(Booking $booking, string $action = 'updated'): bool
    {
        // Keep synchronization disabled until the cloud endpoint is verified.
        if (! config('cloud.zoho_catalyst.sync_enabled')) {
            return false;
        }

        $url = config('cloud.zoho_catalyst.booking_url');
        $secret = env('BOOKING_SYNC_SECRET');

        if (! is_string($url) || trim($url) === '') {
            Log::warning('Zoho booking sync skipped: booking URL is not configured.', [
                'booking_id' => $booking->id,
            ]);

            return false;
        }

        if (! is_string($secret) || trim($secret) === '') {
            Log::warning('Zoho booking sync skipped: secret is not configured.', [
                'booking_id' => $booking->id,
            ]);

            return false;
        }

        try {
            $booking->loadMissing([
                'hotel',
                'guest',
                'rooms.roomType',
            ]);

            $payload = [
                'event_id' => 'booking-' . $booking->id . '-' . $action,
                'action' => $action,
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'hotel_id' => $booking->hotel_id,
                    'hotel_name' => $booking->hotel?->name,
                    'guest_id' => $booking->guest_id,
                    'status' => $booking->status,
                    'check_in_date' => $booking->check_in_date?->toDateString(),
                    'check_out_date' => $booking->check_out_date?->toDateString(),
                    'adults' => $booking->adults,
                    'children' => $booking->children,
                    'total_amount' => $booking->total_amount,
                    'created_at' => $booking->created_at?->toISOString(),
                    'updated_at' => $booking->updated_at?->toISOString(),

                    'rooms' => $booking->rooms->map(function ($room) {
                        return [
                            'id' => $room->id,
                            'room_number' => $room->room_number,
                            'room_type_id' => $room->room_type_id,
                            'room_type' => $room->roomType?->name,
                        ];
                    })->values()->all(),
                ],
            ];

            $certificate = config('cloud.zoho_catalyst.ca_bundle');

            $request = Http::withOptions([
                'verify' => $certificate ?: true,
            ])
                ->withHeaders([
                    'x-booking-sync-secret' => $secret,
                ])
                ->acceptJson()
                ->asJson()
                ->timeout(15);

            $response = $request->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Zoho booking sync failed.', [
                    'booking_id' => $booking->id,
                    'http_status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }

            Log::info('Zoho booking sync succeeded.', [
                'booking_id' => $booking->id,
                'action' => $action,
            ]);

            return true;
        } catch (Throwable $exception) {
            Log::error('Zoho booking sync exception.', [
                'booking_id' => $booking->id,
                'action' => $action,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}