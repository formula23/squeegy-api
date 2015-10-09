<?php namespace App\Http\Controllers;

use App\Discount;
use App\Events\BadRating;
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
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

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

    protected $limit = 10;

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
            $orders->whereIn('status', $filters);
        }

        if($request->input('order_by')) {
            $order_bys=explode(",", $request->input('order_by'));
            foreach($order_bys as $order_by) {
                $order_pts = explode(":", $order_by);
                $orders->orderBy($order_pts[0], (!empty($order_pts[1])?:''));
            }
        }

        foreach(['confirm', 'enroute', 'start', 'done', 'cancel', 'created', 'updated'] as $status_time) {
            if($request->input($status_time.'_on')) {

                $orders->where(\DB::raw('date_format('.$status_time.'_at, "%Y-%m-%d")'), $request->input($status_time.'_on'));

            } else if($request->input($status_time.'_before') || $request->input($status_time.'_after')) {

                foreach(['before'=>'<', 'after'=>'>'] as $when=>$operator) {
                    if( ! $request->input($status_time.'_'.$when)) continue;
                    $orders->where(\DB::raw('date_format('.$status_time.'_at, "%Y-%m-%d")'), $operator, $request->input($status_time.'_'.$when));
                }
            }
        }

        if($request->input('limit')) {
            if((int)$request->input('limit') < 1) $this->limit = 1;
            else $this->limit = min($request->input('limit'), 500);
        }

        $paginator = $orders->paginate($this->limit);

        return $this->response->withPaginator($paginator, new OrderTransformer());
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

//        $promo_code = $this->applyPromoCode($order, $request_data);
//        if( ! $promo_code) {
//            return $this->response->errorWrongArgs(trans('messages.order.discount.unavailable'));
//        }
        $promo_code_msg = $this->applyPromoCode($order, $request_data);
        if($promo_code_msg) {
            return $this->response->errorWrongArgs($promo_code_msg);
        }

        if(isset($request_data['rating']))
        {
            $order->rating = $request_data['rating'];
            $order->rating_comment = $request_data['rating_comment'];

            if($request_data['rating'] < 4) {
                Event::fire(new BadRating($order));
            }
        }

        if(isset($request_data['status']))
        {
            if($this->order_seq[$order->status]===$this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs(trans('messages.order.same_status', ['status'=>$request_data['status']]));
            }

            if($this->order_seq[$request_data['status']]!== 100 &&
                ++$this->order_seq[$order->status] !== $this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs(trans('messages.order.status_change_not_allowed', ['request_status'=>$request_data['status'], 'current_status'=>$order->status]));
            }

            $order->status = $request_data['status'];

            $order->{$order->status."_at"} = Carbon::now();

            switch($order->status)
            {
                case "cancel":

                    if ( ! empty($request_data['cancel_reason']))
                        $order->cancel_reason = $request_data['cancel_reason'];

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

                    $order->eta = Orders::getLeadTime();
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));

                    Event::fire(new OrderConfirmed($order));

                    break;
                case "enroute":

                    if( ! $request->user()->is('worker')) {
                        return $this->response->errorUnauthorized();
                    }

                    $order->worker_id = $request->user()->id;

                    Event::fire(new OrderEnroute($order));

                    break;
                case "start":

                    if( ! $request->user()->is('worker') || $request->user()->id != $order->worker_id) {
                        return $this->response->errorUnauthorized('This order is not assigned to you!');
                    }

                    Event::fire(new OrderStart($order));

                    break;

                case "done":

                    if( ! $request->user()->is('worker') || $request->user()->id != $order->worker_id) {
                        return $this->response->errorUnauthorized('This order is not assigned to you!');
                    }

                    Event::fire(new OrderDone($order));

                    break;
            }

        }

        $order->save();

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

            $discount = Discount::validate_code($request_data['promo_code'], $order);

            if($discount === null) return trans('messages.order.discount.unavailable');

            if($discount->new_customer && ! $order->customer->firstOrder()) return trans('messages.order.discount.new_customer');

            if($discount->user_id && ($order->user_id != $discount->user_id)) return trans('messages.order.discount.unavailable');

            if(Discount::has_regions($discount->id) && ! $discount->regions->count()) return trans('messages.order.discount.out_of_region');

            if($discount->services->count() && ! in_array($order->service_id, $discount->services->lists('id'))) return trans('messages.order.discount.invalid_service', ['service_name' => $order->service->name]);

            if($discount->scope == "system") {

                if( $discount->frequency_rate && $discount->frequency_rate <= Order::where(['discount_id'=>$discount->id, 'status'=>'done'])->get()->count()) return trans('messages.order.discount.unavailable');
            } else {
                if ( ! $order->customer->discountEligible($discount)) return trans('messages.order.discount.unavailable');
            }

            //calculate discount

            $order->discount_id = $discount->id;
            $order->promo_code = $request_data['promo_code'];

            if( $discount->discount_type=='amt' ) {
                $order->discount = $discount->amount * 100;
            } else {
                $order->discount = (int) ($order->price * ($discount->amount / 100));
            }

        }

        return "";
//        return true;
    }

}
