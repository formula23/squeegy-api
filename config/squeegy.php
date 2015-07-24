<?php

return [
    "operating_hours" => [
        "open" => env('OPERATING_HR_OPEN', 10),
        "close" => env('OPERATING_HR_CLOSE', 17),
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
        'bcc' => 'team@squeegyapp.com',
        'from' => 'team@squeegyapp.com',
        'from_name' => 'Team Squeegy',
        'receipt' => [
            'photo_url' => 'https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/orders/',
        ]
    ]
];