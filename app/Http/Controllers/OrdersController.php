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
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Charge as StripeCharge;
use Aloha\Twilio\Twilio;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends ApiGuardController {

    /**
     * @var array
     */
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
        $this->middleware('is.worker', ['only' => 'index']);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $orders = Order::query();

        if($request->input('status')) {
            $filters = explode(',', $request->input('status'));
            $where_method = (is_array($filters)) ? 'whereIn' : 'where' ;
            $orders->{$where_method}('status', $filters);
            if(is_array($filters)) {
                foreach($filters as $filter) {
                    $orders->orderBy($filter.'_at');
                }
            } else {
                $orders->orderBy($filters.'_at');
            }
        }

        return $this->response->withCollection($orders->get(), new OrderTransformer());
    }

	/**
	 * Store a newly created resource in storage.
	 * @param CreateOrderRequest $request
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

    /**
     * @param Order $order
     * @param UpdateOrderRequest $request
     * @param SnsClient $sns_client
     * @param Twilio $twilio
     * @return mixed
     */
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

                    //charge credit card
                    $charged=1000;
                    Stripe::setApiKey(config('stripe.api_key'));
                    $charge = StripeCharge::create([
                        "amount" => $charged,
                        "currency" => "usd",
                        "customer" => $order->customer->stripe_customer_id,
                    ]);
                    $request_data["charged"] = $charged;
                    $request_data["stripe_charge_id"] = $charge->id;

                    $request_data['cancel_at'] = Carbon::now();

                    //send email
                    $email_content = [
                        'name' => $order->customer->name,
                    ];

                    Mail::send('emails.cancellation', $email_content, function ($message) use ($order) {
                        $message->to($order->customer->email, $order->customer->name)->subject(config('squeegy.emails.cancel.subject'));
                    });

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

                    $push_message = 'Hang tight! '.\Auth::user()->name.' is their way!';

                    break;
                case "start":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['start_at'] = Carbon::now();

                    $push_message = $order->worker->name.' has started washing your car.';

                    break;

                case "done":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['done_at'] = Carbon::now();


                    try {
                        $charged = $order->price - (int)$order->discount;

                        //charge the credit card...
                        Stripe::setApiKey(\Config::get('stripe.api_key'));
                        $charge = StripeCharge::create([
                            "amount" => $charged,
                            "currency" => "usd",
                            "customer" => $order->customer->stripe_customer_id,
                        ]);
                        $request_data["charged"] = $charged;
                        $request_data["stripe_charge_id"] = $charge->id;

                    } catch (InvalidRequest $e) {
                        return $this->response->errorWrongArgs($e->getMessage());
                    } catch(\Exception $e) {
                        return $this->response->errorInternalError($e->getMessage());
                    }

                    $push_message = $order->worker->name.' is done washing your car. Your credit card has been charged for $'.number_format($charged/100, 0);

                    //send email
                    $email_content = [
                        'name' => $order->customer->name,
                    ];

                    Mail::send('emails.receipt', $email_content, function ($message) use ($order) {
                        $message->to($order->customer->email, $order->customer->name)->subject(config('squeegy.emails.receipt.subject'));
                    });

                    break;
            }

        }

        $order->update($request_data);

        if($push_message && $order->customer->push_token) {
                $sns_client->publish([
                    'TargetArn' => $order->customer->push_token,
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
     * @param Order $order
     * @return Response
     * @internal param int $id
     */
	public function show(Order $order)
    {
        if (empty($order->id)) {
            return $this->response->errorNotFound();
        }
        return $this->response->withItem($order, new OrderTransformer);
    }
}
