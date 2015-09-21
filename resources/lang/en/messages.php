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
        'new_order_worker' => ':order_service Order #:order_id - :customer_name :customer_phone :eta :vehicle :customer_address :customer_address_lnk',
        'same_status' => 'This order is already in the \':status\' state.',
        'statement_descriptor' => 'Squeegy Car Wash - :service_level',
        'invalid_card' => 'Please enter a valid credit card.',
        'push_notice' => [
            'enroute' => ':worker_name has picked up your order. He will be arriving around :arrival_time',
            'start' => ':worker_name started washing your car.',
            'done' => ':worker_name is done washing your car. We have charged your credit card in the amount of $:charge_amount and emailed you a receipt.',
            'cancel' => 'We\'re very sorry but we had to cancel your order. Contact customer service for additional information.',
        ],
        'cancel_reason' => [
            '1' => 'Customer not here',
            '2' => 'Unable to contact',
            '3' => 'Customer declined work',
            '4' => 'Location not accessible',
        ],
        'discount' => [
            'unavailable' => 'Invalid Promo Code',
            'new_customer' => 'Promotion only available for new customers',
            'out_of_region' => 'Promotion not available in your area',
            'invalid_service' => 'Promotion not available for the :service_name wash',
        ],
    ],
    'service' => [
        'closed' => "Sorry we missed you!\nWe'll be back :next_day, ".env('OPERATING_HR_OPEN')."am - ".(env('OPERATING_HR_CLOSE') - 12).":".env('OPERATING_MIN_CLOSE')."pm",
        'highdemand' => "We are experiencing high-demand.\nPlease try again later.",
    ],
    'emails' => [
        'welcome' => [
            'subject' => 'Welcome to Squeegy!',
        ],
        'receipt' => [
            'subject' => 'Squeegy Receipt - Order# :job_number',
        ],
        'cancel' => [
            'subject' => 'Your Squeegy Order has been cancelled',
        ],
        'bad_rating' => [
            'subject' => 'Squeegy Bad Rating!',
        ]
    ]
];