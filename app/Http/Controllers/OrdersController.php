<?php namespace App\Http\Controllers;

use App\Events\OrderCancelled;
use App\Events\OrderCancelledByWorker;
use App\Events\OrderConfirmed;
use App\Events\OrderDone;
use App\Events\OrderEnroute;
use App\Events\OrderStart;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Squeegy\Orders;
use App\Squeegy\Transformers\OrderTransformer;
use App\Order;
use App\Service;
use Aws\Sns\SnsClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Aloha\Twilio\Twilio;
use Event;
use Illuminate\Support\Facades\Auth;
/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends Controller {

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
            return $this->response->errorUnwillingToProcess(trans('messages.order.exists'));
        }

        //does the vehicle being sent belong to this user
        if( ! $request->user()->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.vehicle_invalid'));
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

        $request_data = $this->applyPromoCode($order, $request_data);

        $push_message = '';

        if(isset($request_data['status']))
        {
            if($this->order_seq[$order->status]===$this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs(trans('messages.order.same_status', ['status'=>$request_data['status']]));
            }

            if($this->order_seq[$request_data['status']]!== 100 &&
                ++$this->order_seq[$order->status] !== $this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs(trans('messages.order.status_change_not_allowed', ['request_status'=>$request_data['status'], 'current_status'=>$order->status]));
            }

            switch($request_data['status'])
            {
                case "cancel":

                    $request_data['cancel_at'] = Carbon::now();

                    if(Auth::user()->is('worker')) {
                        Event::fire(new OrderCancelledByWorker($order));
                    } else {
                        Event::fire(new OrderCancelled($order));
                    }

                    break;
                case "confirm":

                    if( ! $request->user()->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    $availability = Orders::availability();

                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

                    $request_data['lead_time'] = Orders::getLeadTime();
                    $request_data["job_number"] = strtoupper(substr( md5(rand()), 0, 6));
                    $request_data['confirm_at'] = Carbon::now();

                    Event::fire(new OrderConfirmed($order));

                    break;
                case "enroute":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }
                    $request_data['worker_id'] = $request->user()->id;
                    $request_data['enroute_at'] = Carbon::now();

                    Event::fire(new OrderEnroute($order));

                    break;
                case "start":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['start_at'] = Carbon::now();

                    Event::fire(new OrderStart($order));

                    break;

                case "done":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $request_data['done_at'] = Carbon::now();

                    Event::fire(new OrderDone($order));

                    break;
            }

        }

        $order->update($request_data);

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

    /**
     * @param Order $order
     * @param $request_data
     * @return mixed
     */
    protected function applyPromoCode(Order $order, $request_data)
    {
        if (isset($request_data['promo_code'])) { //calculate promo
            if ($request_data['promo_code'] == "1234" && !$order->discount) {
                $request_data['discount'] = 500;
                return $request_data;
            }
            return $request_data;
        }
        return $request_data;
    }

}
