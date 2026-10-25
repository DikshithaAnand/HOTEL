<?php

return [
    'zoho_catalyst' => [
        'booking_url' => env('ZOHO_CATALYST_BOOKING_URL'),
        'bookings_read_url' => env('ZOHO_CATALYST_BOOKINGS_READ_URL'),
        'sync_enabled' => env('ZOHO_CATALYST_SYNC_ENABLED', false),
        'ca_bundle' => 'C:/Users/pc/Downloads/php-8.5.9-Win32-vs17-x64/cacert.pem',
    ],
];