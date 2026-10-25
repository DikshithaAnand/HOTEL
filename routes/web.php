<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Services\CloudBookingService;

// ==========================================
// ADMIN AUTHENTICATION
// ==========================================

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('guest')
    ->name('admin.login.submit');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// ==========================================
// TEMAIRAZU AI — GUEST-FACING ASSISTANT
// ==========================================

Route::get('/temairazu-ai', function () {
    return view('temairazu-ai');
})->name('temairazu.ai');

// ==========================================
// ADMIN DASHBOARD
// ==========================================

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:super_admin'])
    ->name('admin.dashboard');

// ==========================================
// CUSTOMER BOOKING PAGE
// ==========================================

Route::get('/book', function () {
    return view('book');
})->name('customer.book');

// ==========================================
// STORE BOOKINGS
// ==========================================

Route::post('/admin/bookings', [DashboardController::class, 'store'])
    ->middleware(['auth', 'role:super_admin'])
    ->name('admin.bookings.store');

// ==========================================
// ZOHO CATALYST CLOUD CONNECTION STATUS
// ==========================================

Route::get('/cloud-status', function () {

    $certificate = 'C:/Users/pc/Downloads/php-8.5.9-Win32-vs17-x64/cacert.pem';

    $cloudUrl = 'https://hotelcloud-60093144204.development.catalystserverless.in/server/hotel-cloud-check/execute';

    try {
        $response = Http::withOptions([
            'verify' => $certificate,
        ])
            ->timeout(15)
            ->get($cloudUrl);

        if (!$response->successful()) {
            Log::warning('Zoho Catalyst returned an unsuccessful response.', [
                'http_status' => $response->status(),
            ]);

            return response()->json([
                'status' => 'disconnected',
                'message' => 'Cloud service returned an unsuccessful response.',
            ], 502);
        }

        $data = $response->json();
        $output = json_decode($data['output'] ?? '{}', true);

        if (
            is_array($output) &&
            ($output['status'] ?? null) === 'success'
        ) {
            return response()->json([
                'status' => 'connected',
                'message' => $output['message'] ?? 'Cloud connected successfully.',
                'service' => $output['service'] ?? 'Zoho Catalyst',
                'function' => $output['function'] ?? 'hotel-cloud-check',
            ]);
        }

        Log::warning('Unexpected response from Zoho Catalyst.', [
            'response' => $data,
        ]);

        return response()->json([
            'status' => 'disconnected',
            'message' => 'Unexpected response from the cloud function.',
        ], 502);

    } catch (\Throwable $e) {
        Log::error('Zoho Catalyst connection failed.', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => 'disconnected',
            'message' => 'Unable to connect to the cloud service.',
        ], 503);
    }
});

// ==========================================
// ZOHO CATALYST CLOUD BOOKINGS
// ==========================================

Route::get('/cloud-bookings', function (CloudBookingService $cloud) {

    try {
        $bookings = $cloud->getBookings();

        return view('cloud-bookings', [
            'bookings' => $bookings,
            'error' => null,
        ]);

    } catch (\Throwable $e) {
        Log::error('Cloud bookings page failed.', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
        ]);

        return response()->view('cloud-bookings', [
            'bookings' => [],
            'error' => 'Unable to load cloud bookings. Please try again.',
        ], 502);
    }

})->middleware(['auth', 'role:super_admin'])
  ->name('cloud.bookings');