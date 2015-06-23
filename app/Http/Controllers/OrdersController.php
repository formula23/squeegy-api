<?php namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\OctaneLA\Orders;
use App\OctaneLA\Transformers\OrderTransformer;
use App\Order;
use App\Service;
use Aws\Sns\SnsClient;
use Carbon\Carbon;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;
use Aloha\Twilio\Twilio;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends ApiGuardController {

    protected $order_seq = [
        'cancel' => 100,
        'request' => 1,
        'confirm' => 2,
        'enroute' => 3,
        'start' => 4,
        'done' => 5,
    ];

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');

    }



	/**
	 * Store a newly created resource in storage.
	 * @param InitOrderRequest $request
	 * @return Response
	 */
	public function store(CreateOrderRequest $request)
	{
        $data = $request->all();

        //TO-DO:
        //does current user have any washes in progress.. - accept, enroute, start, in-progress,
        if($request->user()->orders()->where('status', 'in', ['confirm','enroute','in-progress'])->get()->count()) {
            return $this->response->errorUnwillingToProcess('User already has an incomplete order.');
        }

        //does the vehicle being sent belong to this user
        if( ! $request->user()->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs('Vehicle id invalid');
        }

        $data['price'] = Service::find($data['service_id'])->price;
        $data['eta'] = Orders::getLeadTime();

        $order = new Order($data);

        $request->user()->orders()->save($order);

        return $this->response->withItem($order, new OrderTransformer);

	}

    public function update(Order $order, UpdateOrderRequest $request, SnsClient $sns_client, Twilio $twilio)
    {
        if(empty($order->id)) {
            return $this->response->errorNotFound();
        }

        $request_data = $request->all();

        if(isset($request_data['promo_code'])) { //calculate promo
            if($request_data['promo_code'] == "1234" && ! $order->discount) {
                $request_data['discount'] = 500;
                $request_data['price'] = $order->price;
            }
        }

        $push_message = '';

        if(isset($request_data['status']))
        {
            if($this->order_seq[$request_data['status']]!== 100 &&
                ++$this->order_seq[$order->status] !== $this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs('Unable to change status. Requested Status: '.$request_data['status'].' - Current Status: '.$order->status);
            }

            switch($request_data['status'])
            {
                case "cancel":

                    if($this->order_seq[$order->status] > 2) {
                        return $this->response->errorWrongArgs('Cannot cancel order any more. Order status:'.$order->status);
                    }

                    $request_data['cancel_at'] = Carbon::now();

                    break;
                case "confirm":

                    if( ! $request->user()->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    if( ! Orders::open() || ! Orders::getLeadTime()) {
                        return $this->response->errorWrongArgs('Service is no longer available.');
                    }

                    $request_data['price'] = Orders::getPrice($order);
                    $request_data['lead_time'] = Orders::getLeadTime();
                    $request_data["job_number"] = strtoupper(substr( md5(rand()), 0, 6));
                    $request_data['confirm_at'] = Carbon::now();

                    $worker_msg = "Squeegy: New Order#".$order->id;
                    if($worker_msg) $twilio->message('+13106004938', $worker_msg);

                    break;
                case "enroute":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }
                    $request_data['worker_id'] = \Auth::user()->id;
                    $request_data['enroute_at'] = Carbon::now();

                    $push_message = "Hang tight, we will be on our way soon!";

                    break;
                case "start":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['start_at'] = Carbon::now();

                    $push_message = 'Sergio has started washing your car...';

                    break;

                case "done":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['end_at'] = Carbon::now();
                    $push_message = 'We are done washing your car. Your credit card has been charged.';

                    try {
                        $charged = $order->price - (int)$order->discount;

                        //charge the credit card...
                        Stripe::setApiKey(\Config::get('stripe.api_key'));
                        $charge = StripeCharge::create([
                            "amount" => $charged,
                            "currency" => "usd",
                            "customer" => $order->user->stripe_customer_id,
                        ]);
                        $request_data["charged"] = $charged;
                        $request_data["stripe_charge_id"] = $charge->id;

                    } catch (InvalidRequest $e) {
                        return $this->response->errorWrongArgs($e->getMessage());
                    } catch(\Exception $e) {
                        return $this->response->errorInternalError($e->getMessage());
                    }

                    break;
            }

        }

        $order->update($request_data);

        if($push_message) {
                $sns_client->publish([
                    'TargetArn' => $order->user->push_token,
                    'MessageStructure' => 'json',
                    'Message' => json_encode([
                        'default' => $push_message,
                        env('APNS') => json_encode([
                            'aps' => [
                                'alert' => $push_message,
                                'sound' => 'default',
                                'badge' => 1
                            ],
                            'order_id' => (string)$order->id,
                        ])
                    ]),
                ]);
        }



        return $this->response->withItem($order, new OrderTransformer);
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Order $order)
    {
        if (empty($order->id)) {
            return $this->response->errorNotFound();
        }
        return $this->response->withItem($order, new OrderTransformer);
    }
}
