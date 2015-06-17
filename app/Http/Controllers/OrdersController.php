<?php namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\OctaneLA\Transformers\OrderTransformer;
use App\Order;
use Aws\Sns\SnsClient;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends ApiGuardController {

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



        //does the vehicle being sent belong to this user
        if( ! \Auth::user()->vehicles()->where('id', '=', $data['vehicle_id'])->get()->count()) {
            return $this->response->errorWrongArgs('Vehicle id invalid');
        }

        $data["job_number"] = strtoupper(substr( md5(rand()), 0, 6));

        $order = new Order($data);

        $myOrder = new \App\OctaneLA\Orders($order);

        $order->price = $myOrder->getPrice();
        $order->status = $myOrder->areWeOpen() ? "accept" : "decline" ;
        $order->lead_time = $myOrder->getLeadTime();

        \Auth::user()->orders()->save($order);

        return $this->response->withItem($order, new OrderTransformer);

	}

    public function update(Order $order, UpdateOrderRequest $request, SnsClient $sns_client)
    {
        if(empty($order->id)) {
            return $this->response->errorNotFound();
        }

        if( ! empty($request->status))
        {
            switch($request->status)
            {
                case "confirm":
                    //send SMS to workers...
                    break;
                case "enroute":
                    //send push notification to app
                    $sns_client->publish([
                        'TargetArn' => $request->user()->push_token,
                        'MessageStructure' => 'json',
                        'Message' => json_encode([
                            'aps' => [
                                'alert' => 'A washer is on his way...',
                                'sound' => 'default',
                                'badge' => 1
                            ],
                            'order_id' => $order->id,
                        ]),
                    ]);
                    break;
                case "start":
                    // in-progess
                    break;
            }
        }

        $order->update($request->all());

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
        if(empty($order->id)) {
            return $this->response->errorNotFound();
        }
        return $this->response->withItem($order, new OrderTransformer);
	}
}
