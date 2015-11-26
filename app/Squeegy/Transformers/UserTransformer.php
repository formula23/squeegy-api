<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/9/15
 * Time: 16:15
 */

namespace App\Squeegy\Transformers;

use App\User;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

    private $validParams = ['limit', 'order'];

    protected $availableIncludes = [
        'roles',
        'orders',
        'vehicles',
    ];

    public function transform(User $user)
    {
        return [
            'id' => (string)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
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

}