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
        'corp_order_cap' => "We're sorry but Squeegy is fully booked for today. We will be back :next_date.",
        'corp_time_slot_cap' => "We're sorry but that time slot is fully booked for today. Please try another time slot.",
        'day_not_available' => 'The requested day is not available. Please go back and try again.',
        'not_accepting_next_day' => "We aren't accepting any more orders at this time.\n\nWe will be back here:\n:date",
        'corp_no_description' => "No additional details at this time.",
        'corp_not_found'=>'Invalid Corporate Partner',
        'confirm_location' => 'We have detected that the location of your vehicle you selected may be incorrect. Please go back and verify your vehicles location.',
        'status_change_not_allowed' => 'Unable to change status. Requested Status: :request_status - Current Status: :current_status',
        'exists' => 'This vehicle already has an open order. Please select or add a different vehicle.',
        'vehicle_invalid' => 'Vehicle id submitted is invalid',
        'new_order_admin' => ':order_service #:order_id - :eta :customer_address :customer_address_lnk',
        'new_order_worker' => ':order_service #:order_id - :eta :customer_address',
        'new_schedule_order' => 'New Scheduled :order_service #:order_id on :scheduled_day between :scheduled_time:location',
        'new_subscription_schedule_order' => 'New Subscription :order_service #:order_id for :subsription_schedule_time',
        'same_status' => 'This order is already in the \':status\' state.',
        'statement_descriptor' => 'SQUEEGY #:job_number',
        'statement_descriptor_tip' => 'SQUEEGY WASHER TIP #:job_number',
        'invalid_card' => 'Please enter a valid credit card.',
        'card_charged' => ' charged your credit card in the amount of $:charge_amount and',
        'push_notice' => [
            'schedule' => "We have received your scheduled wash, a washer will be assigned to you at least one hour prior your scheduled window.\nThank you for using Squeegy!",
            'assign' => ':worker_name has been assigned to your order. He will be arriving around :window_time.',
            'schedule_assign' => ':worker_name has picked up your order. He will be arriving between :window_time.',
            'enroute' => ':worker_name has picked up your order. He will be arriving around :arrival_time.',
            'enroute_manual' => ':worker_name is on the way. He will be arriving around :arrival_time.',
            'start' => ':worker_name started washing your :car. He should be done around :etc_time.',
//            'done' => ":worker_name is done washing your :car. If you would like to review the work, please go outside now. We:card_charged have emailed you a receipt. \nThank you for using Squeegy!",
            'done' => "Your car wash is finished!  Open your Squeegy app now to complete your order.",
            'cancel' => 'Your order has been cancelled. Please contact customer service for additional information.',
            'cancel_washer' => 'Customer cancelled order# :order_id - :vehicle',
            'change_washer' => [
                'customer' => 'Your wash has been re-assigned to :worker_name.',
                'original_washer' => 'Order#:order_id has been removed from your queue. Refresh your washer app.',
                'new_washer' => ':order_service #:order_id has been assigned to you. Refresh your washer app.',
            ],
            'review_wash' => 'Hi :customer_first_name, :washer_name will be done washing your car in about 10 minutes. If you would like to review the work, please go outside now.',
            'arriving_soon' => 'Your washer is arriving soon...',
        ],
        'push_notice_schedule' => [
            'will_cancel' => 'No washers available for Order# :order_id',
            'cancel' => 'We\'re very sorry but there are no washers currently available to perform your wash. We had to cancel your order. A full refund has been issued. Please contact customer service at support@squeegyapp.com for additional information.',
        ],
        'push_notice_corp' => [
            'schedule' => "We have received your car wash order for :schedule_day :schedule_time @ :location\nThank you for using Squeegy!",
            'assign' => ':worker_name has added your vehicle to today\'s job queue.',
            'enroute' => ':worker_name is about to start washing your car. :interior',
        ],
        'push_notice_subscription' => [
            'schedule' => "This is to confirm your next :service_level car wash has been scheduled for your :car on :subsription_schedule_time. No action is required on your behalf. If you have any questions please contact concierge@squeegyapp.com.\nThank you for using Squeegy!",
//            'assign' => ":worker_name will be arriving :window_time to complete your car wash. If you have any questions please contact concierge@squeegyapp.com.\nThank you for using Squeegy!",
//            'done' => ":worker_name is done washing your car. We have emailed you a picture of your car. Don't forget to rate your wash.\nThank you for using Squeegy!",
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
            'partners' => 'Additional discounts not available for this location.',
        ],
        'tip' => [
            'order_not_complete'=>'Wash must be completed to tip.',
            'order_has_tip'=>'Order already has tip!',
        ],
        'communication'=> [
            'invalid_number' => 'Thanks for calling Squeegy. This number is not valid. Goodbye!',
            'invalid_number_sms' => 'Thank you for contacting Squeegy. If you need additional support regarding your wash, please call Squeegy Support at 424-247-8069. Thank you!',
            'sms' => [
                'to_washer'=>":customer_name - Order# :order_id:\n:body",
                'to_customer'=>":washer_name with Squeegy:\n:body",
            ]
        ]
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
        'additional_instructions' => [
            'header'=>'Help us find your vehicle...!',
            'label'=>'Other Instructions....',
            'body'=>"Any relevant information to help us locate and access your vehicle.\nE.g. Building name, specific location, gate code, etc...!",
        ]
        ,
        'partnership_landing' => [
            'header'=>'Squeegy partnerships are mobile car washes at your work or residential building.!!!!',
            'label'=>'Enter Partnership Code!!!',
            'body'=>"Interested in having Squeegy come to your\nwork or residential building?\nTap here to contact us.!!!!",
        ]
    ],
    'washer' => [
        'daily_tip' => [
            'salutation' => "Good morning :washer_name,\n\n",
            'body'=>'You received a total of :tip_amt in tips yesterday. ',
            'motivation' => [
                '10-25'=>"Keep up the good work!",
                '25-40'=>"Your hard work and dedication is really showing in your customer satisfaction. Awesome job!!",
                '40'=>"CONGRATULATIONS! We're happy to see your hard work is really paying off. Keep it up!",
            ],
            'sig'=>"\n\nSqueegy",
        ]
    ]
];