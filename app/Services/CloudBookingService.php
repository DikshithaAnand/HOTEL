<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudBookingService
{
    public function getBookings(): array
    {
        $url = config('cloud.zoho_catalyst.bookings_read_url')
            ?? env('ZOHO_CATALYST_BOOKINGS_READ_URL');

        $secret = env('BOOKING_SYNC_SECRET');

        if (empty($url) || empty($secret)) {
            throw new RuntimeException(
                'Cloud booking URL or secret is not configured.'
            );
        }

        $response = Http::withOptions([
            'verify' => config('cloud.zoho_catalyst.ca_bundle'),
        ])
            ->withHeaders([
                'x-booking-sync-secret' => $secret,
            ])
            ->timeout(20)
            ->get($url);

        $response->throw();

        $data = $response->json();

        if (!is_array($data) || !($data['success'] ?? false)) {
            throw new RuntimeException(
                'Cloud booking service returned an unsuccessful response.'
            );
        }

        return $data['bookings'] ?? [];
    }
}