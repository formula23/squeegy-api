<?php

return [
    "operating_hours" => [
        "open" => 7,
        "close" => 22,
        "max_lead_time" => 120,
    ],
    'service_area' => [
        33.994093,
        -118.452264
    ],
    "sms_verification" => "6538",
    "cancellation_fee" => 1000,
    'emails' => [
        'welcome' => [
            'subject' => 'Welcome to Squeegy!',
        ],
        'receipt' => [
            'subject' => 'Your Squeegy Receipt',
        ],
        'cancel' => [
            'subject' => 'Your Squeegy Order has been cancelled',
        ]
    ]
];