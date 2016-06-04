<?php

return [
    "website_url" => env('WEBSITE_URL', "https://www.squeegyapp.com"),
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
        'support' => 'support@squeegyapp.com',
        'support_name' => 'Squeegy Support',
        'bcc' => 'orders@squeegyapp.com',
        'from' => 'team@squeegyapp.com',
        'from_name' => 'Team Squeegy',
        'receipt' => [
            'photo_url' => 'https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/orders' . (app()->environment('production') ? "/" : "-dev/" ),
        ],
        'tip' => [
            'washer_url' => 'https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/workers' . (app()->environment('production') ? "/" : "-dev/" ),
        ],
    ],
    'order_seq' => [
        'cancel' => 100,
        'test' => 0,
        'request' => 1,
        'confirm' => 2,
        'receive' => 2,
        'schedule' => 2,
        'subscribe' => 2,
        'assign' => 3,
        'enroute' => 4,
        'start' => 5,
        'done' => 6,
    ],
    'use_worker_regions' => env('USE_WORKER_REGIONS', false),
    'worker_default_location' => [
        'lat' => 34.032817,
        'lng' => -118.432363,
    ],
    'referral_program' => [
        'referrer_amt' => env('REFERRER_AMT'),
        'referred_amt' => env('REFERRED_AMT'),
    ],
    'vehicle_surcharge' => [
        '1' => env('EXPRESS_SURCHARGE'),
        '2' => env('CLASSIC_SURCHARGE'),
        '3' => env('SQUEEGY_SURCHARGE'),
    ],
    'groupon_gilt_promotions' => [
        27 => 750,
        28 => 950,
        55 => 900,
        56 => 1200,
        57 => 756,
        58 => 900,
    ]
];