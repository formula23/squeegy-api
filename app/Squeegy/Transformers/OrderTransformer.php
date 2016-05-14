<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\Squeegy\Transformers;

use App\OrderSchedule;
use App\Squeegy\Orders;
use App\Order;
use App\Squeegy\Payments;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;
use Stripe\Card;

class OrderTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'vehicle',
        'service',
        'worker',
        'customer',
        'schedule',
        'order_details',
    ];

    protected $availableIncludes = [
        'referrer',
        'payment_method',
        'partner',
    ];

    public function transform(Order $order)
    {

        $resp = [
            'id' => (string)$order->id,
            'job_number' => $order->job_number,
            'status' => $order->status,
            'is_schedule_order' => ($order->schedule && $order->schedule->type=='one-time'?true:false),
            'is_subscription_order' => ($order->schedule && $order->schedule->type=='subscription'?true:false),
            'location' => $order->location,
            'instructions' => $order->instructions,
            'subtotal' => (int)$order->price,
            'discount' => ($order->discount ? (int)$order->discount : null ),
            'credit' => ($order->credit ? (int)$order->credit : 0 ),
            'total' => (int)($order->price - (int)$order->discount - (int)$order->credit),
            'charged' => (int)($order->charged),
            'promo_code' => ($order->promo_code ? $order->promo_code : null ),
            'eta_quote' => (int)$order->eta,
            'arrival_eta' => $order->arrival_eta(),
            'eta' => Orders::formatConfirmEta($order->eta),
            'eta_seconds' => Orders::getCurrentEta($order),
            'etc' => ($order->start_at ? real_time($order->start_at->addMinutes($order->etc)) : ""),
            'completed_time' => ($order->done_at) ? strtotime($order->done_at) : null,
            'photo_count' => $order->photo_count,
            'rating' => $order->rating,
            'rating_comment' => $order->rating_comment,
            'platform' => $order->push_platform,

            'confirm_time' => $order->confirm_at ? ($order->schedule ? $order->created_at->format('n/d g:ia') : $order->confirm_at->format('g:ia') ) : "",
            'assign_time' => $order->assign_at ? date("g:ia", strtotime($order->assign_at)) : "",
            'enroute_time' => $order->enroute_at ? date("g:ia", strtotime($order->enroute_at)) : "",
            'start_time' => $order->start_at ? date("g:ia", strtotime($order->start_at)) : "",
            'done_time' => $order->done_at ? date("g:ia", strtotime($order->done_at)) : "",
            'cancel_time' => $order->cancel_at ? date("g:ia", strtotime($order->cancel_at)) : "",

            'confirm_at' => $order->confirm_at ? (object) ["date"=>$order->confirm_at->format("Y-m-d H:i:s")] : null,
            'assign_at' => $order->assign_at ? (object) ["date"=>$order->assign_at->format("Y-m-d H:i:s")] : null,
            'enroute_at' => $order->enroute_at ? (object) ["date"=>$order->enroute_at->format("Y-m-d H:i:s")] : null,
            'start_at' => $order->start_at ? (object) ["date"=>$order->start_at->format("Y-m-d H:i:s")] : null,
            'done_at' => $order->done_at ? (object) ["date"=>$order->done_at->format("Y-m-d H:i:s")] : null,
            'cancel_at' => $order->cancel_at ? (object) ["date"=>$order->cancel_at->format("Y-m-d H:i:s")] : null,

            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('v1.orders.show', ['orders'=>$order->id])
                ]
            ],
        ];

        if($order->schedule) {
            $resp['eta'] = $order->scheduled_eta();
        } else {
            $arrival_eta = eta_real_time($order);
            if($arrival_eta) {
                $resp['eta'] = $arrival_eta;
            }
        }

        $current_eta = current_eta($order);
        if($current_eta) {
            $resp['eta'] = $current_eta;
        }

        if($order->auth_transaction) {
            $resp['card']['brand'] = $order->auth_transaction->card_type;
            $resp['card']['last4'] = $order->auth_transaction->last_four;
        }

        return $resp;

    }

    public function includeSchedule(Order $order)
    {
        $schedule = ($order->schedule?:new OrderSchedule());
        return $this->item($schedule, new OrderScheduleTransformer);
    }

    public function includeReferrer(Order $order) {
        $referrer = ($order->referrer?:new User());
        return $this->item($referrer, new UserTransformer);
    }

    public function includeCustomer(Order $order) {
        return $this->item($order->customer, new UserTransformer);
    }

    public function includeService(Order $order)
    {
        $order->service->name = $order->service->getOriginal('name');
        if(($order_surcharge = $order->hasSurCharge()) && !request()->user()->is('worker')) {
            $order->service->name = $order->service->getOriginal('name')." + $".number_format($order_surcharge/100)."(".$order->vehicle->type.")";
        }
        
        return $this->item($order->service, new ServiceTransformer);
    }

    public function includeWorker(Order $order)
    {
        $worker = $order->worker;
        if(!$worker) $worker = new \App\User;
        return $this->item($worker, new UserTransformer);
    }

    public function includeVehicle(Order $order)
    {
        $vehicle = $order->vehicle;
        return $this->item($vehicle, new VehicleTransformer);
    }

    public function includePaymentMethod(Order $order)
    {
        $card= new \stdClass();
        if($order->auth_transaction) {
            $card->brand = $order->auth_transaction->card_type;
            $card->last4 = $order->auth_transaction->last_four;
            $card->exp_month=null;
            $card->exp_year=null;
        } else {
            if($order->stripe_charge_id) {
                $payments = new Payments($order->customer->stripe_customer_id);
                $card = $payments->card_charged($order->stripe_charge_id);
            }
        }

        return $this->item($card, new PaymentMethodTransformer());
    }

    public function includePartner(Order $order)
    {
        if(!$order->partner) return null;
        return $this->item($order->partner, new PartnerTransformer());
    }

    public function includeOrderDetails(Order $order)
    {

    }
}
