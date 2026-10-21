<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meal Cutoff
    |--------------------------------------------------------------------------
    |
    | Students may opt in, opt out or change a meal until this many hours
    | before the scheduled serve time. The server clock is authoritative.
    |
    */

    'cutoff_hours' => (int) env('MESS_CUTOFF_HOURS', 4),

    /*
    |--------------------------------------------------------------------------
    | Display Timezone
    |--------------------------------------------------------------------------
    |
    | Meal times are stored in UTC and rendered in this timezone for students.
    |
    */

    'display_timezone' => env('MESS_DISPLAY_TIMEZONE', 'Asia/Kolkata'),

    /*
    |--------------------------------------------------------------------------
    | Counter Lanes
    |--------------------------------------------------------------------------
    |
    | Bookings are pinned to one of these lanes when the meal is locked so a
    | student can only redeem their meal at the counter they were assigned.
    |
    */

    'lanes' => ['A', 'B', 'C', 'D'],

    /*
    |--------------------------------------------------------------------------
    | Billing
    |--------------------------------------------------------------------------
    |
    | All monetary values are stored and calculated as integer paise.
    |
    */

    'monthly_base_paise' => (int) env('MESS_MONTHLY_BASE_PAISE', 300000),

    'rebate_per_day_paise' => (int) env('MESS_REBATE_PER_DAY_PAISE', 5000),

    /*
    |--------------------------------------------------------------------------
    | Dietary Tags
    |--------------------------------------------------------------------------
    */

    'dietary_tags' => [
        'veg' => 'Veg',
        'non-veg' => 'Non-Veg',
        'jain' => 'Jain',
        'eggitarian' => 'Eggitarian',
        'vegan' => 'Vegan',
    ],

    /*
    |--------------------------------------------------------------------------
    | QR Check-in Tokens (ES256)
    |--------------------------------------------------------------------------
    |
    | Tokens are signed with an ECDSA P-256 private key and verified by
    | scanners with the public key, including fully offline.
    |
    */

    'qr' => [
        'ttl_seconds' => 60,
        'refresh_seconds' => 45,
        'issuer' => env('MESS_QR_ISSUER', 'zerowastemess.io'),
        'audience' => env('MESS_QR_AUDIENCE', 'zerowastemess-scanner'),
        'private_key_path' => env('MESS_QR_PRIVATE_KEY_PATH', storage_path('app/keys/qr-private.pem')),
        'public_key_path' => env('MESS_QR_PUBLIC_KEY_PATH', storage_path('app/keys/qr-public.pem')),
        'openssl_config' => env('MESS_QR_OPENSSL_CONF'),
    ],

];
