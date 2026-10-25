<?php

use App\Http\Controllers\Api\TemairazuAIController;
use App\Http\Controllers\Api\V1\Customer\CustomerAuthController;
use App\Http\Controllers\Api\V1\Customer\CustomerAvailabilityController;
use App\Http\Controllers\Api\V1\Customer\CustomerBookingController;
use Illuminate\Support\Facades\Route;

// =====================================================
// TEMAIRAZU AI — GUEST-FACING CHAT API
// =====================================================

Route::post('/temairazu-ai/chat', [
    TemairazuAIController::class,
    'chat',
])->middleware('throttle:api')->name('temairazu-ai.chat');

// =====================================================
// EXISTING API ROUTES
// =====================================================

Route::prefix('v1')
    ->middleware('throttle:api')
    ->scopeBindings()
    ->group(function () {
        require base_path('routes/api/auth.php');

        // Public customer room availability
        Route::get('/customer/availability', [
            CustomerAvailabilityController::class,
            'index',
        ])->name('customer.availability');

        // Customer registration and login
        Route::post('/customer/register', [
            CustomerAuthController::class,
            'register',
        ])->middleware('throttle:auth-login');

        Route::post('/customer/login', [
            CustomerAuthController::class,
            'login',
        ])->middleware('throttle:auth-login');

        // Authenticated customer routes
        Route::middleware('auth:sanctum')
            ->prefix('customer')
            ->group(function () {
                Route::get('/me', [
                    CustomerAuthController::class,
                    'me',
                ]);

                Route::post('/logout', [
                    CustomerAuthController::class,
                    'logout',
                ]);

                Route::get('/bookings', [
                    CustomerBookingController::class,
                    'index',
                ]);

                Route::post('/bookings', [
                    CustomerBookingController::class,
                    'store',
                ]);
            });

        // Existing protected administration APIs
        Route::middleware(['auth:sanctum', 'tenancy'])->group(function () {
            require base_path('routes/api/security.php');
            require base_path('routes/api/admin.php');
            require base_path('routes/api/hotels.php');
            require base_path('routes/api/rooms.php');
            require base_path('routes/api/guests.php');
            require base_path('routes/api/services.php');
            require base_path('routes/api/housekeeping.php');
            require base_path('routes/api/maintenance.php');
            require base_path('routes/api/bookings.php');
            require base_path('routes/api/notifications.php');
            require base_path('routes/api/billing.php');
            require base_path('routes/api/users.php');
            require base_path('routes/api/reports.php');
            require base_path('routes/api/pricing.php');
        });
    });