<?php

return [
    "operating_hours" => [
        "open" => 0,
        "close" => 23,
        "max_lead_time" => 120,
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