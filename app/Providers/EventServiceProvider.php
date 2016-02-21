<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;



class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'event.name' => [
			'EventListener',
		],

		'App\Events\UserCreated' => [
			'App\Handlers\Events\SegmentUser',
		],

        'App\Events\UserRegistered' => [
            'App\Handlers\Events\SendWelcomeEmail',
        ],

        'App\Events\OrderCancelled' => [
            'App\Handlers\Events\ChargeCancelFee',
        ],

        'App\Events\OrderCancelledByWorker' => [
            'App\Handlers\Events\ChargeCancelFee',
            'App\Handlers\Events\NotifyCustomerCancel',
        ],

        'App\Events\OrderConfirmed' => [
            'App\Handlers\Events\AuthOrder',
            'App\Handlers\Events\NotifyWorkerNewOrder',
        ],

		'App\Events\OrderScheduled' => [
			'App\Handlers\Events\AuthOrder',
			'App\Handlers\Events\NotifyAdminNewOrder',
			'App\Handlers\Events\NotifyCustomerSchedule',
		],

		'App\Events\OrderAssign' => [
			'App\Handlers\Events\NotifyCustomerAssign',
			'App\Handlers\Events\NotifyWorkerNewOrder',
		],

        'App\Events\OrderEnroute' => [
            'App\Handlers\Events\NotifyCustomerEnroute',
        ],

        'App\Events\OrderStart' => [
            'App\Handlers\Events\NotifyCustomerStart',
        ],

        'App\Events\OrderDone' => [
            'App\Handlers\Events\ChargeOrder',
			'App\Handlers\Events\CreditReferrer',
			'App\Handlers\Events\SegmentUser',
//			'App\Handlers\Events\MarkSingleUseDiscount',
            'App\Handlers\Events\NotifyCustomerDone',
            'App\Handlers\Events\SendReceiptEmail',
        ],

        'App\Events\BadRating' => [
            'App\Handlers\Events\EmailSupport',
        ]
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		//
	}

}
