<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/25/15
 * Time: 16:49
 */

return [
    'profile' => [
        'phone_verify' => 'Squeegy verification code: :verify_code',
    ],
    'order' => [
        'status_change_not_allowed' => 'Unable to change status. Requested Status: :request_status - Current Status: :current_status',
        'exists' => 'You already have an order pending.',
        'vehicle_invalid' => 'Vehicle id submitted is invalid',
        'new_order_worker' => 'New Order# :order_id - :customer_name :customer_phone',
        'same_status' => 'This order is already in the \':status\' state.',
        'statement_descriptor' => 'Squeegy Car Wash - :service_level',
        'push_notice' => [
            'enroute' => 'Hang tight! :worker_name is on the way!',
            'start' => ':worker_name started washing your car.',
            'done' => ':worker_name is done washing your car. Your credit card has been charged $:charge_amount',
            'cancel' => 'We\'re very sorry but we had to cancel your order. Contact customer service for additional information.',
        ],
    ],
    'service' => [
        'closed' => 'You have reached us after hours.',
        'highdemand' => 'Due to high-demand, we can\'t take your order request at the moment.',
    ],
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