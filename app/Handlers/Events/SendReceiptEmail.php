<?php namespace App\Handlers\Events;

use App\Events\OrderDone;
use Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Stripe\Card;
use Stripe\Customer;
use Stripe\Stripe;

class SendReceiptEmail {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderComplete  $event
	 * @return void
	 */
	public function handle(OrderDone $event)
	{
        $order = $event->order;
        //send email
        $email_content = [
            'name' => $order->customer->name,
        ];

        try {
            Mail::send('emails.receipt', $email_content, function ($message) use ($order) {

                $vehicle =& $order->vehicle;
                $customer =& $order->customer;
                $location = $order->location;

                $default_card=null;
                if($order->charged > 0) {
                    //get stripe customer
                    Stripe::setApiKey(config('services.stripe.secret'));
                    $stripe_customer = Customer::retrieve($customer->stripe_customer_id);
                    $default_card = $stripe_customer->sources->retrieve($stripe_customer->default_source);
                }

                $mergevars = [
                    'ORDER_ID' => $order->id,
                    'ORDER_DATE' => $order->done_at->format('m/d/Y'),
                    'ORDER_TIME' => $order->done_at->format('g:i a'),
                    'ORDER_NUMBER' => $order->job_number,
                    'SERVICE' => $order->service->name.' Wash',
                    'SERVICE_PRICE' => number_format($order->price/100, 2),
                    'EXTRAS' => '0.00',
                    'SUBTOTAL' => number_format($order->price/100, 2),
                    'PROMO' => $order->promo_code,
                    'DISCOUNT_AMOUNT' => number_format($order->discount/100, 2),
                    'CHARGED' => number_format($order->charged/100, 2),
                    'SHOW_CHARGE' => ($order->charged ? true : false),
                    'CUSTOMER_NAME' => $customer->name,
                    'VEHICLE' => $vehicle->year." ".$vehicle->make." ".$vehicle->model." (".$vehicle->color.")",
                    'VEHICLE_PIC' => config('squeegy.emails.receipt.photo_url').$order->id.'.jpg',
                    'LICENSE_PLATE' => $vehicle->license_plate,
                    'ADDRESS' => $location['street'].", ".$location['city'].", ".$location['state']." ".$location['zip'],
                ];

                if($default_card) {
                    $mergevars['CC_TYPE'] = $default_card->brand;
                    $mergevars['CC_LAST4'] = $default_card->last4;
                }

                $headers = $message->getHeaders();
                $headers->addTextHeader('X-MC-MergeVars', json_encode($mergevars));
                $headers->addTextHeader('X-MC-Template', 'receipt');

                $message->from(config('squeegy.emails.from'), config('squeegy.emails.from_name'));
                $message->bcc(config('squeegy.emails.bcc'));

                $message->to($customer->email, $customer->name)
                    ->subject(trans('messages.emails.receipt.subject', ['job_number' => $order->job_number]));
            });
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }

	}

}
