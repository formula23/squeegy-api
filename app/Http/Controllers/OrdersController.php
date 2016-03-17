<?php namespace App\Http\Controllers;

use App\Discount;
use App\Events\BadRating;
use App\Events\OrderAssign;
use App\Events\OrderCancelled;
use App\Events\OrderCancelledByWorker;
use App\Events\OrderConfirmed;
use App\Events\OrderDone;
use App\Events\OrderEnroute;
use App\Events\OrderScheduled;
use App\Events\OrderStart;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\OrderDetail;
use App\OrderSchedule;
use App\Squeegy\Orders;
use App\Squeegy\Transformers\OrderTransformer;
use App\Order;
use App\Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends Controller {

    /**
     * @var array
     */
    protected $order_seq = null;

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

        $this->order_seq = Config::get('squeegy.order_seq');

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $orders = Order::query();

        $orders->with('vehicle')
            ->with('service')
            ->with('worker')
            ->with('customer');

        if(Auth::user()->is('customer|worker')) {
            if(Auth::user()->is('worker')) {
                $orders->where('worker_id', Auth::user()->id)
                    ->orderBy('confirm_at', 'asc');
//                $this->limit = 1;
            } else {
                $orders->where('user_id', Auth::user()->id);
            }
        } else {
            //exclude internal test orders
//            $orders->whereNotIn('user_id', function ($q) {
//                $q->select('id')
//                    ->from('users')
//                    ->where('email', 'like', '%formula23%')
//                    ->orWhere('email', 'like', '%sinister%')
//                    ->orWhere('email', 'like', '%squeegy%')
//                    ->orWhere('email', 'like', '%testing%')
//                    ->orWhere('email', 'like', '%triet.luong%');
//            });
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

        foreach(['confirm', 'assign', 'enroute', 'start', 'done', 'cancel', 'created', 'updated'] as $status_time) {
            if($request->input($status_time.'_on')) {

                $orders->where(\DB::raw('date_format('.$status_time.'_at, "%Y-%m-%d")'), $request->input($status_time.'_on'));

            } else if($request->input($status_time.'_before') || $request->input($status_time.'_after')) {

                foreach(['before'=>'<=', 'after'=>'>='] as $when=>$operator) {
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

        $is_schedule = ( !empty($data['day']) && !empty($data['time_slot']) ? true : false );

        //does current user have any washes in progress for the requested vehicle
        if( !$is_schedule && $request->user()->orders()->whereIn('status', ['assign','enroute','start'])->where('vehicle_id', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.exists'));
        }

        //does the vehicle being sent belong to this user
        if( ! $request->user()->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.vehicle_invalid'));
        }

        if($request->header('X-Device')=="Android") $data['push_platform'] = 'gcm';

        $service = Service::find($data['service_id']);

        $data['price'] = 0;

        $order_details=[];
        $order_details[] = new OrderDetail(['name'=>$service->name, 'amount'=>$service->price]);
        $data['price'] += $service->price;

        $eta = Orders::getLeadTime($data['location']['lat'], $data['location']['lon']);
        Log::info('OrdersController@store:117');
        Log::info($eta);
        try {

            if( ! empty($eta['schedule']) && (empty($data['day']) || empty($data['time_slot']))) return $this->response->errorWrongArgs(trans('messages.service.schedule_param_req'));

            if($is_schedule) {

                list($window_open, $window_close) = explode("-", $data['time_slot']);

                $schedule_data = [
                    'window_open' => new Carbon($data['day']." ".$window_open),
                    'window_close' => new Carbon($data['day']." ".$window_close),
                ];

                if($schedule_data['window_open']->isPast()) return $this->response->errorWrongArgs(trans('messages.service.schedule_in_past'));

                $order_schedule = OrderSchedule::create([
                    'window_open' => new Carbon($data['day']." ".$window_open),
                    'window_close' => new Carbon($data['day']." ".$window_close),
                ]);

            } else { //on-demand
                if(!empty($eta['time'])) {
                    $data['eta'] = $eta['time'];
                } else {
                    \Log::error(trans('messages.service.not_available'));
                    \Log::info($eta);
                    \Log::info("Request params:");
                    \Log::info($data);
                    \Bugsnag::notifyException(new \Exception(trans('messages.service.not_available')));
                    return $this->response->errorWrongArgs(trans('messages.service.not_available'));
                }
            }
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
            return $this->response->errorWrongArgs(trans('messages.service.error'));
        }

        $data['total'] = $data['price'];

        ///use available credits
        if($request->user()->availableCredit()) {
            $data['credit'] = min($service->price, $request->user()->availableCredit());
            $data['total'] -= $data['credit'];
        }

//        \DB::enableQueryLog();

        $order = new Order($data);
        $request->user()->orders()->save($order);

        if($surcharge = $order->vehicleSurCharge()) {
            $order->price += $surcharge;
            $order->total = $order->price;
            $order_details[] = new OrderDetail(['name'=>'Surcharge', 'amount'=>$surcharge]);
        }

        $order->order_details()->saveMany($order_details);

        if( ! empty($order_schedule)) {
            $order->schedule()->save($order_schedule);
        }

        $order->save();

//        dd(\DB::getQueryLog());

        return $this->response->withItem($order, new OrderTransformer());

	}

    /**
     * @param Order $order
     * @param UpdateOrderRequest $request
     * @return mixed
     */
    public function update(Order $order, UpdateOrderRequest $request)
    {
//        \DB::enableQueryLog();

        if(empty($order->id) || (Auth::user()->is('customer') && Auth::id()!=$order->user_id)) {
            return $this->response->errorNotFound('Order not found');
        }

        $request_data = $request->all();

        $promo_code_msg = $this->applyPromoCode($order, $request_data);
        if($promo_code_msg) {
            return $this->response->errorWrongArgs($promo_code_msg);
        }

        if( ! empty($request_data['rating']))
        {

            $order->rating = $request_data['rating'];
            $order->rating_comment = (!empty($request_data['rating_comment'])?$request_data['rating_comment']:"");

            if($request_data['rating'] < 4) {
                Event::fire(new BadRating($order));
            }
        } else if( ! empty($request_data['rating_comment'])) {
            $order->rating_comment = $request_data['rating_comment'];
            if($order->rating < 4) {
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
            $original_status = $order->status;

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
                case "confirm": //v1.4 uses this status

                    if( ! $request->user()->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    $availability = Orders::availability($order->location['lat'], $order->location['lon']);
                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

                    $order->eta = $availability['time'];
                    $order->etc = $order->service->time;
                    $order->worker_id = $availability['worker_id'];
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));

                    Event::fire(new OrderConfirmed($order));

                    $order->status = 'enroute';
                    $order->enroute_at = Carbon::now();

                    Event::fire(new OrderEnroute($order));

                    break;

                case "receive": //v1.5 uses this status
                    if( ! $request->user()->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    $availability = Orders::availability($order->location['lat'], $order->location['lon']);

                    Log::info('RECEIVE', $availability);
                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

                    if($availability['postal_code'] == '90015' && strtolower($order->promo_code) != "joymode20") {
                        return $this->response->errorWrongArgs("You need a valid promo code to order a Squeegy wash in this area. Contact support@squeegyapp.com");
                    }

                    $order->confirm_at = Carbon::now();
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));
                    $order->etc = $order->service->time;

                    unset($order->receive_at);

                    if($order->schedule) {
                        $order->confirm_at = $order->schedule->window_open;
                        $order->status = 'schedule';
                        Event::fire(new OrderScheduled($order));

                    } else {

                        $order->status = 'assign';
                        $order->assign_at = Carbon::now();
                        $order->eta = $availability['time'];
                        $order->worker_id = $availability['worker_id'];

                        Event::fire(new OrderConfirmed($order));
                    }

                    break;
                case "assign":

                    if( ! $request->user()->is('admin')) {
                        return $this->response->errorUnauthorized();
                    }

                    if($original_status != "schedule") {
                        return $this->response->errorUnauthorized('Unable to assign. Current status: '.$original_status);
                    }

                    if($request_data['worker_id']) {
                        $order->worker_id = $request_data['worker_id'];
                    } else {
                        $availability = Orders::availability($order->location['lat'], $order->location['lon']);
                        try {
                            $order->eta = $availability['time'];
                            $order->worker_id = $availability['worker_id'];
                        } catch(\Exception $e) {
                            return $this->response->errorWrongArgs('Unable to assign order.');
                        }
                    }

                    Event::fire(new OrderAssign($order));

                    break;
                case "enroute":

                    if( ! $request->user()->can('order.status')) {
                        return $this->response->errorUnauthorized();
                    }

                    $order->worker_id = $request->user()->id;

                    Event::fire(new OrderEnroute($order, false));

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
            'worker_id',
        ];

        foreach($update_fields as $update_field) {
            if(isset($request_data[$update_field])) {
                $order->{$update_field} = $request_data[$update_field];
            }
        }

        $order->push();

//        Log::info(\DB::getQueryLog());

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
        if(Auth::user()->is('customer') && Auth::id()!=$order->user_id) {
            return $this->response->errorNotFound('Order not found');
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
        if(Auth::user()->is('customer') && Auth::id()!=$order->user_id) {
            return $this->response->errorNotFound('Order not found');
        }

        if (isset($request_data['promo_code'])) { //calculate promo

            //check if promo code is a referral code
            if($referrer = User::where('referral_code', $request_data['promo_code'])->where('id','!=',\Auth::user()->id)->first())
            {
                //referrer program only valid for new customers
                if( ! $order->customer->firstOrder()) return trans('messages.order.discount.referral_code_new_customer');

                $order->referrer_id = $referrer->id;
                $order->promo_code = $request_data['promo_code'];
                $order->discount = (int)Config::get('squeegy.referral_program.referred_amt');
            }
            else
            {
                $discount = Discount::validate_code($request_data['promo_code'], $order);

                if($discount === null) return trans('messages.order.discount.unavailable');

                if($discount->new_customer && ! $order->customer->firstOrder()) return trans('messages.order.discount.new_customer');

                if($discount->user_id && ($order->user_id != $discount->user_id)) return trans('messages.order.discount.unavailable');

                if(Discount::has_regions($discount->id) && ! $discount->regions->count()) return trans('messages.order.discount.out_of_region');

                if($discount->services->count() && ! in_array($order->service_id, $discount->services->lists('id')->all())) return trans('messages.order.discount.invalid_service', ['service_name' => $order->service->name]);

                $scope_discount = true;
                $frequency_rate = 0;
                if($discount->scope == "system") {
                    $scope_label="";
                    if($discount->frequency_rate && $discount->frequency_rate <= $discount->active_orders->count()) {
                        $scope_discount = false;
                        $frequency_rate = $discount->frequency_rate;
                    }

                    if($discount->discount_code) {
                        $actual_discount_code = $discount->actual_discount_code($request_data['promo_code']);
                        if( ! $actual_discount_code) return trans('messages.order.discount.unavailable');

                        if($actual_discount_code->frequency_rate &&
                            $actual_discount_code->frequency_rate <= Order::where('promo_code', $request_data['promo_code'])->whereNotIn('status', ['cancel','request'])->count())
                        {
                            $frequency_rate = $actual_discount_code->frequency_rate;
                            $scope_discount = false;
                        }
                    }
                } else {
                    $scope_label=" per customer";

                    if($discount->discount_code) {
                        $actual_code = $discount->actual_discount_code($request_data['promo_code']);
                        if(!$actual_code) return trans('messages.order.discount.unavailable');

                        if($actual_code->frequency_rate > 0) {

                            if( ! (Order::device_orders('promo_code', $request_data['promo_code'])->count() < $actual_code->frequency_rate) ||
                                ! (Auth::user()->orders_with_discount('promo_code', $request_data['promo_code'])->count() < $actual_code->frequency_rate))
                            {
                                $frequency_rate = $actual_code->frequency_rate;
                                $scope_discount = false;
                            }
                        }
                    }

                    if($discount->frequency_rate) {
                        if( ! (Order::device_orders('discount_id', $discount->id)->count() < $discount->frequency_rate) ||
                            ! (Auth::user()->orders_with_discount('discount_id', $discount->id)->count() < $discount->frequency_rate))
                        {
                            $frequency_rate = $discount->frequency_rate;
                            $scope_discount = false;
                        }
                    }
                }

                if( ! $scope_discount) {
                    switch($frequency_rate) {
                        case 1:
                        case 2:
                            $word_map = ['once','twice'];
                            $times = $word_map[($frequency_rate-1)];
                            break;
                        default:
                            $times = $frequency_rate." ".str_plural('time', $frequency_rate);
                            break;
                    }
                    return trans('messages.order.discount.frequency', ['times'=>$times, 'scope_label'=>$scope_label]);
                }

                //calculate discount
                $order->discount_id = $discount->id;
                $order->promo_code = $request_data['promo_code'];


                if( $discount->discount_type=='amt' ) {
                    $order->discount = $discount->amount;
                } else {
                    $order->discount = (int) ($order->price * ($discount->amount / 100));
                }

                if($order->discount > $order->price) $order->discount = $order->price;
            }

            $order->credit = min($order->price - $order->discount, $order->customer->availableCredit());
            $order->total = max(0,$order->price - $order->discount - $order->credit);

        }

        return "";
    }

}
