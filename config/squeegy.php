<?php

return [
    "operating_hours" => [
        "open" => env('OPERATING_HR_OPEN', 10),
        "close" => env('OPERATING_HR_CLOSE', 18),
        "max_lead_time" => 120,
    ],
    'service_area' => [
        33.994093,
        -118.452264
    ],
    "sms_verification" => "6538",
    "cancellation_fee" => 1000,
    'emails' => [
        'support' => 'feedback@squeegyapp.com',
        'bcc' => 'orders@squeegyapp.com',
        'from' => 'team@squeegyapp.com',
        'from_name' => 'Team Squeegy',
        'receipt' => [
            'photo_url' => 'https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/orders' . (app()->environment('production') ? "/" : "-dev/" ),
        ]
    ],
    'order_seq' => [
        'cancel' => 100,
        'request' => 1,
        'confirm' => 2,
        'enroute' => 3,
        'start' => 4,
        'done' => 5,
    ],
    'use_worker_regions' => env('USE_WORKER_REGIONS', false),
    'worker_default_location' => [
        'lat' => 34.032817,
        'lng' => -118.432363,
    ],
    'referral_program' => [
        'referrer_amt' => env('REFERRER_AMT'),
        'referred_amt' => env('REFERRED_AMT'),
    ]
];