<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/25/15
 * Time: 16:49
 */

use Illuminate\Support\Facades\Config;


return [
    'profile' => [
        'phone_verify' => 'Squeegy verification code: :verify_code',
    ],
    'order' => [
        'status_change_not_allowed' => 'Unable to change status. Requested Status: :request_status - Current Status: :current_status',
        'exists' => 'This vehicle already has an open order. Please select or add a different vehicle.',
        'vehicle_invalid' => 'Vehicle id submitted is invalid',
        'new_order_admin' => ':order_service #:order_id - :eta :customer_address :customer_address_lnk',
        'new_order_worker' => ':order_service #:order_id - :eta',
        'new_schedule_order' => 'New Scheduled :order_service #:order_id on :scheduled_day between :scheduled_time',
        'same_status' => 'This order is already in the \':status\' state.',
        'statement_descriptor' => 'SQUEEGY #:job_number',
        'invalid_card' => 'Please enter a valid credit card.',
        'push_notice' => [
            'schedule' => 'We have received your scheduled wash, a washer will be assigned to you at least one hour prior your scheduled window. Thank you for using Squeegy!',
            'assign' => ':worker_name has picked up your order. He will be arriving between :window_time',
            'enroute' => ':worker_name has picked up your order. He will be arriving around :arrival_time',
            'enroute_manual' => ':worker_name is on the way. He will be arriving around :arrival_time',
            'start' => ':worker_name started washing your car.',
            'done' => ':worker_name is done washing your car. We have charged your credit card in the amount of $:charge_amount and emailed you a receipt. Don\'t forget to rate your wash. Thank you for using Squeegy!',
            'cancel' => 'We\'re very sorry but we had to cancel your order. Contact customer service for additional information.',
        ],
        'push_notice_corp' => [
            'schedule' => 'We have received your car wash order for :schedule_day. Thank you for using Squeegy!',
            'assign' => ':worker_name has added your vehicle to today\'s job queue.',
            'enroute' => ':worker_name is about to start your car wash. :interior',
        ],
        'push_notice_subscription' => [
            'schedule' => 'Your next car wash has been scheduled for :subsription_schedule_time. If you have any questions please contact concierge@squeegyapp.com. Thank you for using Squeegy!',
            'assign' => ':worker_name will be arriving :window_time to complete your car wash. If you have any questions please contact concierge@squeegyapp.com. Thank you for using Squeegy!',
            'done' => ':worker_name is done washing your car. We have emailed you a receipt. Don\'t forget to rate your wash. Thank you for using Squeegy!',
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
            'referral_code_new_customer' => 'Referral codes can only be used by new customers.',
        ],
    ],
    'service' => [
        'closed' => "Sorry we missed you!\nWe'll be back :next_day, ".env('OPERATING_HR_OPEN')."am - ".(env('OPERATING_HR_CLOSE') - 12).":close_mins",
        'highdemand' => "Due to high-demand we cannot take your order.\nPlease try again later.",
        'outside_area' => "Outside of service area.\nTap here to go to our service area.",
        'not_available' => 'Squeegy not available at this time. Please try again later.',
        'error' => 'There was an error. Please try again.',
        'schedule_param_req' => 'To schedule an order, please select a day and time.',
        'only_schedule' => 'Only scheduling available at this time. Please go back and try again.',
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
    ],
    'app_copy' => [
        'referral_program' => [
            'header' => 'Invite friends. Get free washes.',
            'body' => 'Give a friend $'.(Config::get('squeegy.referral_program')['referred_amt']/100).' credit towards their first car wash and earn a $'.(Config::get('squeegy.referral_program')['referrer_amt']/100).' credit yourself.',
            'share_msg' => 'Hey, I use Squeegy to wash my car on-demand and want to send you $'.(Config::get('squeegy.referral_program')['referred_amt']/100).' to try it. Use my referral code:',
            'share_link' => 'Download the app here: https://www.squeegyapp.com/free-washes/',
            'email_subject' => 'Get $'.(Config::get('squeegy.referral_program')['referred_amt']/100).' off your first car wash using Squeegy on-demand car wash!',
        ],
        'create_password' => [
            'header' => 'Create a Password',
            'body' => 'We\'ve added many new features to this version of Squeegy. To continue, please add a password to your account.',
        ],
    ],
];