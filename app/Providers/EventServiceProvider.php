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

        'App\Events\UserRegistered' => [
            'App\Handlers\Events\SendWelcomeEmail',
            'App\Handlers\Events\SendSMSVerification',
        ],

        'App\Events\OrderCancelled' => [
            'App\Handlers\Events\ChargeCancelFee',
        ],

        'App\Events\OrderCancelledByWorker' => [
//            'App\Handlers\Events\RefundOrder',
            'App\Handlers\Events\ChargeCancelFeeWorker',
            'App\Handlers\Events\NotifyCustomerCancel',
        ],

        'App\Events\OrderConfirmed' => [
            'App\Handlers\Events\AuthOrder',
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
            'App\Handlers\Events\NotifyCustomerDone',
            'App\Handlers\Events\SendReceiptEmail',
        ],
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
