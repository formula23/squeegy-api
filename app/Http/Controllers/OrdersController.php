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
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    protected $limit = null;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if($request->header('Authorization')) {
            $this->middleware('jwt.auth');
        } else {
            $this->middleware('auth');
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $orders = Order::query();

        if(Auth::user()->is('customer|worker')) {
            if(Auth::user()->is('worker')) {
                $orders->where('worker_id', Auth::user()->id)
                    ->orderBy('confirm_at', 'asc');
//                $this->limit = 1;
            } else {
                $orders->where('user_id', Auth::user()->id);
            }
        }

        if($request->input('job_number')) {
            $orders->where('job_number', $request->input('job_number'));
        }

        if($request->input('status')) {
            $filters = explode(',', $request->input('status'));
            $orders->whereIn('status', $filters);
        }

        if($request->input('order_by')) {
            $order_bys=explode(",", $request->input('order_by'));
            foreach($order_bys as $order_by) {
                $order_pts = explode(":", $order_by);
                $orders->orderBy($order_pts[0], ( ! empty($order_pts[1])?$order_pts[1]:''));
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

        if($request->input('worker_id')) {
            $orders->where('worker_id', $request->input('worker_id'));
        }

        if($request->input('limit')) {
            if((int)$request->input('limit') < 1) $this->limit = 1;
            else $this->limit = $request->input('limit');
        }
//dd($orders);
        $paginator = $orders->paginate($this->limit);

        return $this->response->withPaginator($paginator, new OrderTransformer());
    }

    public function all_locations()
    {
        $orders = Order::select("location")->where('status', 'done')->get();
        return $this->response->withArray($orders->toArray());
    }

	/**
	 * Store a newly created resource in storage.
	 * @param CreateOrderRequest $request
	 * @return Response
	 */
	public function store(CreateOrderRequest $request)
	{
        $data = $request->all();

        //does current user have any washes in progress for the requested vehicle
        if($request->user()->orders()->whereIn('status', ['confirm','assign','enroute','start'])->where('vehicle_id', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.exists'));
        }

        //does the vehicle being sent belong to this user
        if( ! $request->user()->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.vehicle_invalid'));
        }

        $data['price'] = Service::find($data['service_id'])->price;

        $eta = Orders::getLeadTime($data['location']['lat'], $data['location']['lon']);
        try {
            $data['eta'] = $eta['time'];
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
            return $this->response->errorWrongArgs(trans('messages.service.outside_area'));
        }

        $order = new Order($data);

        $request->user()->orders()->save($order);

        return $this->response->withItem($order, new OrderTransformer);

	}

    /**
     * @param Order $order
     * @param UpdateOrderRequest $request
     * @return mixed
     */
    public function update(Order $order, UpdateOrderRequest $request)
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

                    if(Auth::user()->can('order.status')) {
                        Event::fire(new OrderCancelledByWorker($order));
                    } else {
                        Event::fire(new OrderCancelled($order));
                    }

                    break;
                case "confirm":

                    if( ! $request->user()->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    $availability = Orders::availability($order->location['lat'], $order->location['lon']);
                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

//                    $eta = Orders::getLeadTimeByOrder($order);

                    $order->eta = $availability['time'];
                    $order->etc = $order->service->time;
                    $order->worker_id = $availability['worker_id'];
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));

                    Event::fire(new OrderConfirmed($order));

                    $order->status = 'enroute';
                    $order->enroute_at = Carbon::now();

                    Event::fire(new OrderEnroute($order));

                    break;
                case "enroute":

                    if( ! $request->user()->can('order.status')) {
                        return $this->response->errorUnauthorized();
                    }

                    $order->worker_id = $request->user()->id;

                    Event::fire(new OrderEnroute($order));

                    break;
                case "start":

                    if( ! $request->user()->can('order.status') || $request->user()->id != $order->worker_id) {
                        return $this->response->errorUnauthorized('This order is not assigned to you!');
                    }

                    Event::fire(new OrderStart($order));

                    break;

                case "done":

                    if( ! $request->user()->can('order.status') || $request->user()->id != $order->worker_id) {
                        return $this->response->errorUnauthorized('This order is not assigned to you!');
                    }

                    Event::fire(new OrderDone($order));

                    break;
            }

        }

        //update other fields
        $update_fields = [
            'start_at',
            'done_at',
        ];

        foreach($update_fields as $update_field) {
            if(isset($request_data[$update_field])) {
                $order->{$update_field} = $request_data[$update_field];
            }
        }

        $order->save();

        return $this->response->withItem($order, new OrderTransformer());
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
                if($discount->frequency_rate && $discount->frequency_rate <= $discount->active_orders->count()) return trans('messages.order.discount.unavailable');

                if($discount->discount_code) {
                    $actual_discount_code = $discount->actual_discount_code($request_data['promo_code']);
                    if(!$actual_discount_code) return trans('messages.order.discount.unavailable');

                    if($actual_discount_code->frequency_rate &&
                        $actual_discount_code->frequency_rate <= Order::where('promo_code', $request_data['promo_code'])->whereIn('status', ['assign','enroute','start','done'])->count())
                    {
                        return trans('messages.order.discount.unavailable');
                    }
                }
            } else {
                if ( ! $order->customer->discountEligible($discount, $request_data['promo_code'])) return trans('messages.order.discount.unavailable');
            }

            //calculate discount

            $order->discount_id = $discount->id;
            $order->promo_code = $request_data['promo_code'];

            if( $discount->discount_type=='amt' ) {
                $order->discount = $discount->amount;
            } else {
                $order->discount = (int) ($order->price * ($discount->amount / 100));
            }

        }

        return "";
//        return true;
    }

}
