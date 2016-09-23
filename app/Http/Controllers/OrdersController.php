<?php namespace App\Http\Controllers;

use App\Discount;
use App\Events\BadRating;
use App\Events\ChangeWasher;
use App\Events\OrderAssign;
use App\Events\OrderCancelled;
use App\Events\OrderCancelledByWorker;
use App\Events\OrderConfirmed;
use App\Events\OrderDone;
use App\Events\OrderEnroute;
use App\Events\OrderScheduled;
use App\Events\OrderStart;
use App\FailedSmsLog;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\OrderDetail;
use App\OrderSchedule;
use App\OrderSmsLog;
use App\Squeegy\Emails\Tip;
use App\Squeegy\Orders;
use App\Squeegy\Payments;
use App\Squeegy\Transformers\OrderTransformer;
use App\Order;
use App\Service;
use App\Partner;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

use Services_Twilio_Twiml as TwilioTwiml;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends Controller {

    /**
     * @var array
     */
    protected $order_seq = null;

    protected $limit = 100;

    protected $order_date;

    protected $black_list_device_id = [
        '0F6B1F21-15F0-4A43-B75C-C88B8516F2A5', //user id: 866 Steph
        '4C274C2A-3704-43EA-9B2F-736675AA6C51', //user id: 2876 Steve Merker
        '69026923-8D9D-42DF-8DE2-A51E43B77102', //user id: 6858 Jillian E.
        'EC781C46-43B7-45B3-B76C-15E4FB4AACF3', //user id: 9624 Natali Gonzalez
    ];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if($request->header('Authorization')) {
            $this->middleware('jwt.auth', ['except'=> ['connectVoice', 'connectSms']]);
        } else {
            $this->middleware('auth', ['except'=> ['connectVoice', 'connectSms']]);
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
                $orders->where('worker_id', Auth::user()->id);
//                    ->orderBy('confirm_at', 'asc');
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
                if($order_pts[0]=="confirm_at") {
                    $orderby = \DB::raw('IF(orders.eta, confirm_at + INTERVAL orders.eta MINUTE, confirm_at)');
                } else {
                    $orderby = $order_pts[0];
                }

                $orders->orderBy($orderby, ( ! empty($order_pts[1]) ? $order_pts[1] : 'asc' ));
            }
        }

        if($request->input('zip')) {
            $orders->where('location' ,'like', "%".$request->input('zip')."%");
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

        $partner_id = $request->input('partner_id');
        if($partner_id!="") {
            if ($partner_id === "0") {
                $orders->whereNull('partner_id');
            } else if($partner_id == "all-partners") {
                $orders->whereNotNull('partner_id');
            } else {
                $orders->where('partner_id', $partner_id);
            }
        }

        if($request->input('limit')) {
            if((int)$request->input('limit') < 1) $this->limit = 1;
            else $this->limit = $request->input('limit');
        }
        
        $paginator = $orders->paginate($this->limit);

        return $this->response->withPaginator($paginator, new OrderTransformer());
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
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

        $user = $request->user();
        if($request->input('user_id')) {
            $user = User::find($request->input('user_id'));
            if( ! $user) return $this->response->errorWrongArgs('User not found');
        }

//        Log::info($request->header('X-Device-Identifier'));

        if(in_array($request->header('X-Device-Identifier'), $this->black_list_device_id)) {
            return $this->response->errorUnauthorized();
        }

        if($data['location']['lat'] == 33.9861737 && $data['location']['lon'] == -118.3977665) {
            return $this->response->errorWrongArgs(trans('messages.order.confirm_location'));
        }

        //does current user have any washes in progress for the requested vehicle
        if( !$is_schedule && $user->orders()->whereIn('status', ['assign','enroute','start'])->where('vehicle_id', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.exists'));
        }

        //does the vehicle being sent belong to this user
        if( ! $user->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs(trans('messages.order.vehicle_invalid'));
        }

        if($request->header('X-Device')=="Android") $data['push_platform'] = 'gcm';

        $service = Service::find($data['service_id']);

        $eta = Orders::getLeadTime($data['location']['lat'], $data['location']['lon']);
        Log::info('OrdersController@store:117');
        Log::info($eta);
        try {

            if( ! empty($eta['schedule']) && (empty($data['day']) || empty($data['time_slot']))) return $this->response->errorWrongArgs(trans('messages.service.schedule_param_req'));

            if($is_schedule) {

                list($window_open, $window_close) = explode("-", $data['time_slot']);

                $schedule_data = [];
                $schedule_data['window_open'] = new Carbon($data['day']." ".$window_open);

                if($window_close) {
                    $schedule_data['window_close'] = new Carbon($data['day']." ".$window_close);
                } else {
                    $schedule_data['window_close'] = $schedule_data['window_open'];
                    $schedule_data['type'] = 'subscription';
                }

//                $time_window_key = ( ! empty($data['partner_id']) ? 'close' : 'open' );
                $time_window_key = 'close';
                if($schedule_data['window_'.$time_window_key]->isPast()) return $this->response->errorWrongArgs(trans('messages.service.schedule_in_past'));


                //** partner stuff *//
                if(isset($data['partner_id'])) {
                    $partner = Partner::find($data['partner_id']);
                    $data['location'] = $partner->location;

                } else {
                    $partner = Partner::where_coords_in($data['location']['lat'], $data['location']['lon']);
                    $data['partner_id'] = $partner->id;
                }
                
                if($partner) {
                    $service = $partner->service($data['service_id'])->first();

//                    $this->validate_partner_day($partner, $schedule_data['window_open']);

                    $day = $partner->get_day_by_date($schedule_data['window_open']);
                    if( ! $day) return $this->response->errorWrongArgs(trans('messages.order.day_not_available'));

                    try {
                        if(($accepting_code = $day->accept_order($schedule_data['window_open'])) < 0) {
                            return $this->return_partner_resp($accepting_code, $day);
                        }

                    } catch(\Exception $e) {
                        \Bugsnag::notifyException($e);
                        \Log::info($e);
                    }
                }


//                if($partner = Partner::where_coords_in($data['location']['lat'], $data['location']['lon'])) {
//
//                    $service = $partner->service($data['service_id'])->first();
//
////                    $this->validate_partner_day($partner, $schedule_data['window_open']);
//
//                    $day = $partner->get_day_by_date($schedule_data['window_open']);
//                    if( ! $day) return $this->response->errorWrongArgs(trans('messages.order.day_not_available'));
//
//                    try {
//
//                        if(($accepting_code = $day->accept_order($schedule_data['window_open'])) < 0) {
//                            return $this->return_partner_resp($accepting_code, $day);
//                        }
//
//                    } catch(\Exception $e) {
//                        \Bugsnag::notifyException($e);
//                        \Log::info($e);
//                    }
//
//                }

                $order_schedule = OrderSchedule::create($schedule_data);
                $this->order_date = $schedule_data['window_open'];

            } else { //on-demand
                $this->order_date = Carbon::now();
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
            \Log::info($e);
            return $this->response->errorWrongArgs(trans('messages.service.error'));
        }

        $order_details=[];
        $order_details[] = new OrderDetail(['name'=>$service->name, 'amount'=>$service->price($this->order_date)]);

        $data['price'] = 0;
        $data['price'] += $service->price($this->order_date);

        $data['total'] = $data['price'];

//        \DB::enableQueryLog();

        $order = new Order($data);

        $user->orders()->save($order);

        if($surcharge = $order->vehicleSurCharge()) {
            $order->price += $surcharge;
            $order->total = $order->price;
            $order_details[] = new OrderDetail(['name'=>$order->vehicle->type.' Surcharge', 'amount'=>$surcharge]);
        }

        $order->order_details()->saveMany($order_details);

        if( ! empty($order_schedule)) {
            $order->schedule()->save($order_schedule);
        }

        ///use available credits
        if( ! $order->isSubscription() && $user->availableCredit()) {
            $order->credit = min($order->total, $user->availableCredit());
            $order->total -= $order->credit;
        }

        $order->save();

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

        $user = $request->user();

        if(empty($order->id) || ($user->is('customer') && $user->id != $order->user_id)) {
            return $this->response->errorNotFound('Order not found');
        }

        $request_data = $request->all();
        
        $promo_code_msg = $order->applyPromoCode(@$request_data['promo_code']);
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
//            print $request_data['status'];
//            dd($order->status);
            if($this->order_seq[$order->status]===$this->order_seq[$request_data['status']]) {
                return $this->response->errorWrongArgs(trans('messages.order.same_status', ['status'=>$request_data['status']]));
            }

            if($this->order_seq[$order->status] == 100) {
                return $this->response->errorWrongArgs('Error! Order is currently cancelled.');
            }

            if($this->order_seq[$request_data['status']]!== 100 &&
                ++$this->order_seq[$order->status] !== $this->order_seq[$request_data['status']] &&
                ! $user->can('order.status')) {
                return $this->response->errorWrongArgs(trans('messages.order.status_change_not_allowed', ['request_status'=>$request_data['status'], 'current_status'=>$order->status]));
            }
            $original_status = $order->status;

            $order->status = $request_data['status'];

            if($order->status!='test') {
                $order->{$order->status."_at"} = Carbon::now();
            }
            
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

                    $order->phone = null;

                    break;
                case "confirm": //v1.4 uses this status

                    if( ! $user->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    $availability = Orders::availability($order->location['lat'], $order->location['lon']);
                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

                    $order->eta = $availability['actual_time'];
                    $order->etc = $order->get_etc();
                    $order->worker_id = $availability['worker_id'];
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));

                    Event::fire(new OrderConfirmed($order));

                    $order->status = 'enroute';
                    $order->enroute_at = Carbon::now();

                    Event::fire(new OrderEnroute($order));

                    break;

                case "receive": //v1.5 uses this status

                    if( ! $user->is('admin') && ! $user->is('customer')) {
                        return $this->response->errorUnauthorized();
                    }

                    if($order->partner) {
//                        $this->validate_partner_day($order->partner, $order->schedule->window_open);

                        $day = $order->partner->get_day_by_date($order->schedule->window_open);
                        if( ! $day) return $this->response->errorWrongArgs(trans('messages.order.day_not_available'));

                        if(($accepting_code = $day->accept_order($order->schedule->window_open)) < 0) {
                            return $this->return_partner_resp($accepting_code, $day);
                        }
                    }

                    $availability = Orders::availability($order->location['lat'], $order->location['lon']);

                    Log::info('RECEIVE', $availability);
                    if( ! $availability['accept']) {
                        return $this->response->errorWrongArgs($availability['description']);
                    }

                    if( $availability['accept'] && $availability['schedule'] && ! $order->schedule ) {
                        return $this->response->errorWrongArgs(trans("messages.service.only_schedule"));
                    }
                    
//                    if(in_array($availability['postal_code'], ['91316','91356','91335','91406','91436']) && strtolower($order->promo_code) != "ktla") {
//                        \Bugsnag::notifyException(new \Exception("Order attempt in Encino without promo code. User id: ".$user->id));
//                        return $this->response->errorWrongArgs("You need a valid promo code to order a Squeegy wash in this area.");
//                    }
                    
                    $order->confirm_at = Carbon::now();
                    $order->job_number = strtoupper(substr( md5(rand()), 0, 6));
                    $order->etc = $order->get_etc();

                    unset($order->receive_at);

                    try {
                        if($order->schedule) {
                            $order->confirm_at = $order->schedule->window_open;
                            $order->status = 'schedule';
                            Event::fire(new OrderScheduled($order));

                        } else {

                            $order->status = 'assign';
                            $order->assign_at = Carbon::now();
                            $order->eta = $availability['actual_time'];
                            $order->worker_id = $availability['worker_id'];

                            Event::fire(new OrderConfirmed($order));
                        }
                    } catch (\Exception $e) {
                        return $this->response->errorWrongArgs($e->getMessage());
                        \Bugsnag::notifyException($e);
                    }

                    break;
                case "assign":

                    if( ! $user->can('order.assign')) {
                        return $this->response->errorUnauthorized();
                    }

                    if(config('squeegy.order_seq')[$original_status] < config('squeegy.order_seq')[$order->status]) { //forward
                        if($request_data['worker_id']) {
                            $order->worker_id = $request_data['worker_id'];
                        } else {
                            $availability = Orders::availability($order->location['lat'], $order->location['lon']);
                            try {
                                $order->eta = $availability['actual_time'];
                                $order->worker_id = $availability['worker_id'];
                            } catch(\Exception $e) {
                                return $this->response->errorWrongArgs('Unable to assign order.');
                            }
                        }

                        Event::fire(new OrderAssign($order));
                    }


                    break;
                case "enroute":

                    if( ! $user->can('order.status')) return $this->response->errorUnauthorized('You don\'t have permission');

                    if( $user->is('worker') && $user->id != $order->worker_id) return $this->response->errorUnauthorized('This order is not assigned to you!');

                    if(!$order->worker_id) $order->worker_id = $user->id;

                    if(!$order->isPartner() && $order->worker->orders()->whereIn('status', ['enroute','start'])->whereNull('partner_id')->count())  return $this->response->errorUnauthorized('Please finish current job, before going to next...');

                    Event::fire(new OrderEnroute($order, false));

                    break;
                case "start":

                    if( ! $user->can('order.status')) return $this->response->errorUnauthorized('You don\'t have permission');

                    if( $user->is('worker') && $user->id != $order->worker_id) return $this->response->errorUnauthorized('This order is not assigned to you!');

                    Event::fire(new OrderStart($order));

                    break;

                case "done":

                    if( ! $user->can('order.status')) return $this->response->errorUnauthorized('You don\'t have permission');

                    if( $user->is('worker') && $user->id != $order->worker_id) return $this->response->errorUnauthorized('This order is not assigned to you!');

                    $order->phone = null;

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
     * @param Request $request
     * @param Order $order
     * @return mixed
     */
    public function changeWasher(Request $request, Order $order)
    {
        if( ! $order->exists) return $this->response->errorNotFound();

        if($this->order_seq[$order->status] > 4) return $this->response->errorMethodNotAllowed('Can\'t change washer! Order is complete or cancelled.');
        
        $order->worker_id = $request->input('worker_id');

        Event::fire(new ChangeWasher($order));

        $order->save();
        return $this->response->withItem($order, new OrderTransformer());
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return mixed
     */
    public function tipWasher(Request $request, Order $order)
    {
        if( ! $order->exists) return $this->response->errorNotFound();
        
        if($this->order_seq[$order->status] != 6) return $this->response->errorMethodNotAllowed(trans('messages.order.tip.order_not_complete'));
        if($order->tip!==null) return $this->response->errorWrongArgs(trans('messages.order.tip.order_has_tip'));

        $tip_amount = $request->input('amount');
        $order->tip = (int)$tip_amount;
        $order->tip_at = Carbon::now();

        if($tip_amount)
        {
            $description = substr(trans('messages.order.statement_descriptor_tip', ['job_number'=>$order->job_number]), 0, 20);

            //charge tip amount
            $payments = new Payments($order->customer->stripe_customer_id);
            $charge  = $payments->sale($tip_amount, $order, $description);

            //record transaction
            $order->transactions()->create([
                'charge_id'=>$charge->id,
                'amount'=>$charge->amount,
                'type'=>'sale',
                'last_four'=>$charge->source->last4,
                'card_type'=>$charge->source->brand,
            ]);

            //send email to customer
            try {
                (new Tip)
                    ->withBCC(config('squeegy.emails.bcc'))
                    ->withData(['data' => $order])
                    ->sendTo($order->customer);
            } catch(\Exception $e) {
                \Bugsnag::notifyException(new \Exception($e->getMessage()));
            }
        }

        $order->save();

        return $this->response->withItem($order, new OrderTransformer());
    }

    public function changeService(Request $request, Order $order)
    {
        if( ! $order->exists) return $this->response->errorNotFound();

        if($order->status > 5) return $this->response->errorWrongArgs('Order already complete! Can not change service level.');

        if(($service_id = $request->input('service_id')) == $order->service_id) {
            return $this->response->errorWrongArgs('Order already this service level. Nothing to change.');
        }

        $order->change_service($service_id);

        return $this->response->withItem($order, new OrderTransformer());
    }

    public function connectVoice(Request $request)
    {
        $twilioNumber = $request->input('To');
        $incomingNumber = $request->input('From');

        //get order by twilio number
        $order = Order::getOrderFromNumber($twilioNumber);
        if( ! $order) return $this->failedVoiceResponse();

        $order_recipients = $order->getContactRecipients($incomingNumber);

        if(count($order_recipients)) {
            return response($this->connectVoiceResponse($order_recipients['to']->phone, $twilioNumber, $order_recipients['to_type']))->header('Content-Type', 'application/xml');
        }

        return $this->failedVoiceResponse();
    }

    public function connectSms(Request $request)
    {
        $twilioNumber = $request->input('To');
        $incomingNumber = $request->input('From');
        $messageBody = $request->input('Body');

//        \Log::info('twilio: '.$twilioNumber);
//        \Log::info('incoming: '.$incomingNumber);
//        \Log::info('msg: '.$messageBody);

        $order = Order::getOrderFromNumber($twilioNumber);
//        \Log::info($order);
        if( ! $order) return $this->failedSmsResponse($twilioNumber, $incomingNumber, $messageBody);

        $order_recipients = $order->getContactRecipients($incomingNumber);

//        \Log::info($order_recipients);

        if(count($order_recipients)) {
            $messageBody = $this->getSmsMessage($incomingNumber, $order, $messageBody);

            $order->save_sms_log($order_recipients, $messageBody);
            return response($this->connectSmsResponse($messageBody, $order_recipients['to']->phone))->header('Content-Type', 'application/xml');
        }

        return $this->failedSmsResponse($twilioNumber, $incomingNumber, $messageBody);
    }

    private function connectVoiceResponse($outgoingNumber, $twilioNumber, $toType)
    {
        $response = new TwilioTwiml();
        try {

            $response->play(config('squeegy.s3.bucket').'/media/phone_calls/'.( $toType=='washer' ? 'CustomerCall' : 'WasherCall' ).'.mp3');
            $response->dial($outgoingNumber, [
                'callerId' => $twilioNumber,
                'record'=>true,
            ]);
        } catch(\Exception $e) {
            \Log::info($e);
            \Bugsnag::notifyException($e);
        }
        return $response;
    }

    private function connectSmsResponse($messageBody, $outgoingNumber)
    {
        $response = new TwilioTwiml();
        try {
            $response->message(
                $messageBody,
                ['to' => $outgoingNumber]
            );
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }
        return $response;
    }

    private function failedVoiceResponse()
    {
        $response = new TwilioTwiml();
        $response->play(config('squeegy.s3.bucket').'/media/phone_calls/Invalid.mp3');
        return $response;
    }

    private function failedSmsResponse($to, $from, $message)
    {
        $response = new TwilioTwiml();
        try {
            $response->message(
                trans('messages.order.communication.invalid_number_sms'),
                ['to' => $from]
            );
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

        try {
            FailedSmsLog::create([
                'to'=>$to,
                'from'=>$from,
                'message'=>$message,
            ]);
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

        return $response;
    }

    private function getSmsMessage($incomingNumber, $order, $messageBody)
    {
        if($incomingNumber === $order->customer->phone) {
            $messageBody = trans('messages.order.communication.sms.to_washer', [
                'customer_name'=> $order->customer->first_name(),
                'order_id'=>$order->id,
                'body'=>$messageBody,
            ]);
        } else {

            $messageBody = trans('messages.order.communication.sms.to_customer', [
                'washer_name'=>$order->worker->first_name(),
                'body'=>$messageBody,
            ]);
        }
        return $messageBody;
    }

    private function return_partner_resp($err_code, $day)
    {
        if($err_code == -2) {
            return $this->response->errorWrongArgs(trans("messages.order.corp_time_slot_cap"));
        } else {
            return $this->response->errorWrongArgs(trans("messages.order.corp_order_cap", ['next_date'=>$day->next_date_on_site()->format('l, M jS')]));
        }
    }

    private function validate_partner_day($partner, $requested_date)
    {
        $day = $partner->get_day_by_date($requested_date);
        if( ! $day) return $this->response->errorWrongArgs(trans('messages.order.day_not_avilable'));

        try {

            if(($accepting_code = $day->accept_order($requested_date)) < 0) {
                return $this->return_partner_resp($accepting_code, $day);
            }

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
            \Log::info($e);
        }

        return;
    }

}
