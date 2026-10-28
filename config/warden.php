<?php

return [
    'initial' => [
        'name' => env('WARDEN_INITIAL_NAME'),
        'phone' => env('WARDEN_INITIAL_PHONE'),
        'email' => env('WARDEN_INITIAL_EMAIL'),
        'employee_id' => env('WARDEN_INITIAL_EMPLOYEE_ID'),
        'hostel' => env('WARDEN_INITIAL_HOSTEL'),
        'registered_at' => env('WARDEN_INITIAL_REGISTERED_AT'),
        'password' => env('WARDEN_INITIAL_PASSWORD'),
    ],
    'second' => [
        'name' => env('WARDEN_SECOND_NAME'),
        'phone' => env('WARDEN_SECOND_PHONE'),
        'email' => env('WARDEN_SECOND_EMAIL'),
        'employee_id' => env('WARDEN_SECOND_EMPLOYEE_ID'),
        'hostel' => env('WARDEN_SECOND_HOSTEL'),
        'registered_at' => env('WARDEN_SECOND_REGISTERED_AT'),
        'password' => env('WARDEN_SECOND_PASSWORD'),
    ],
];
