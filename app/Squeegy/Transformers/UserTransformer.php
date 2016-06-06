<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/9/15
 * Time: 16:15
 */

namespace App\Squeegy\Transformers;

use App\PaymentMethod;
use App\Squeegy\Payments;
use App\User;
use App\WasherActivityLog;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

    private $validParams = ['limit', 'order'];

    protected $availableIncludes = [
        'roles',
        'orders',
        'vehicles',
        'activity_logs',
        'latest_activity_log',
        'payment_methods',
        'zones',
        'notes',
    ];

    public function transform(User $user)
    {
        return [
            'id' => (string)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'referral_code' => $user->referral_code,
            'is_active' => $user->is_active,
            'anon_pw_reset' => (bool)$user->anon_pw_reset,
            'is_anon' => $user->is_anon(),
            'available_credits' => $user->availableCredit(),
            'facebook_id' => $user->facebook_id,
            'segment_id' => ($user->segment?$user->segment->segment_id:''),
        ];
    }

    public function includeRoles(User $user)
    {
        return $this->collection($user->getRoles(), new RoleTransformer);
    }

    public function includeOrders(User $user, ParamBag $params = null)
    {
        // Optional params validation
//        dd($params);
//        $usedParams = array_keys(iterator_to_array($params));
//        if ($invalidParams = array_diff($usedParams, $this->validParams)) {
//            throw new \Exception(sprintf('Invalid param(s): "%s". Valid param(s): "%s"', implode(',', $usedParams), implode(',', $this->validParams)));
//        }
        $orders_qry = $user->orders();

        if($params) {
            list($limit, $offset) = $params->get('limit');
            list($orderCol, $orderBy) = $params->get('order');

            if($limit && $offset) {
                $orders_qry->take($limit)->skip($offset);
            }

            if($orderCol && $orderBy) {
                $orders_qry->orderBy($orderCol, $orderBy);
            }

            $status = $params->get('status');
            if($status) {
                $orders_qry->whereIn('status',$status);
            }
        }

        $orders = $orders_qry->get();

        return $this->collection($orders, new OrderTransformer());
    }

    public function includeVehicles(User $user)
    {
        return $this->collection($user->vehicles, new VehicleTransformer());
    }

    public function includeActivityLogs(User $user)
    {
        return $this->collection($user->activity_logs, new WasherActivityLogTransformer());
    }

    public function includePaymentMethods(User $user)
    {
        $payments = new Payments($user->stripe_customer_id);
        $cards = $payments->cards();
        return $this->collection($cards, new PaymentMethodTransformer());
    }

    public function includeLatestActivityLog(User $user)
    {
        $washer_log = new WasherActivityLog();
        $washer_log->login = $user->login;
        $washer_log->logout = $user->logout;
        $washer_log->log_on = $user->log_on;
        $washer_log->log_off = $user->log_off;

        return $this->item($washer_log, new WasherActivityLogTransformer());
    }

    public function includeZones(User $user)
    {
        if( ! $user->is('worker')) return null;

        return $this->collection($user->zones, new ZoneTransformer());
    }

    public function includeNotes(User $user, ParamBag $params = null)
    {
        $notes_qry = $user->notes();

        if($params) {
            list($limit, $offset) = $params->get('limit');
            list($orderCol, $orderBy) = $params->get('order');

            if($limit && $offset) {
                $notes_qry->take($limit)->skip($offset);
            }

            if($orderCol && $orderBy) {
                $notes_qry->orderBy($orderCol, $orderBy);
            }
        }

        $notes = $notes_qry->get();
        return $this->collection($notes, new UserNoteTransformer());
    }

}