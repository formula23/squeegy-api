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
        'exists' => 'This vehicle already has an open order. Please select or add a different vehicle.',
        'vehicle_invalid' => 'Vehicle id submitted is invalid',
        'new_order_worker' => ':order_service #:order_id - :eta :customer_address :customer_address_lnk',
        'new_schedule_order' => 'New Scheduled :order_service #:order_id on :scheduled_day between :scheduled_time',
        'same_status' => 'This order is already in the \':status\' state.',
        'statement_descriptor' => 'Squeegy Car Wash - :service_level',
        'invalid_card' => 'Please enter a valid credit card.',
        'push_notice' => [
            'assign' => ':worker_name has picked up your order. He will be arriving between :window_time',
            'enroute' => ':worker_name has picked up your order. He will be arriving around :arrival_time',
            'enroute_manual' => ':worker_name is on the way. He will be arriving around :arrival_time',
            'start' => ':worker_name started washing your car.',
            'done' => ':worker_name is done washing your car. We have charged your credit card in the amount of $:charge_amount and emailed you a receipt. Don\'t forget to rate your wash. Thanks for using Squeegy!',
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
            'frequency' => 'Promotion can only be used :times:scope_label',
            'new_customer' => 'Promotion only available for new customers',
            'out_of_region' => 'Promotion not available in your area',
            'invalid_service' => 'Promotion not available for the :service_name wash',
            'referral_code_new_customer' => 'Referral codes are only valid for new customers.',
        ],
    ],
    'service' => [
        'closed' => "Sorry we missed you!\nWe'll be back :next_day, ".env('OPERATING_HR_OPEN')."am - ".(env('OPERATING_HR_CLOSE') - 12).":close_mins",
        'highdemand' => "Due to high-demand we cannot take your order.\nPlease try again later.",
        'outside_area' => "Outside of service area.\nTap here to go to our service area.",
        'not_available' => 'Squeegy not available at this time. Please try again later.',
        'error' => 'There was an error. Please try again.',
        'schedule_param_req' => 'To schedule an order, please select a day and time.',
        'schedule_in_past' => 'A wash can\'t be scheduled in the past.',
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