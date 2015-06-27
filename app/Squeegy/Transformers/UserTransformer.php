<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/9/15
 * Time: 16:15
 */

namespace App\Squeegy\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'roles',
    ];

    public function transform(User $user)
    {
        return [
            'id' => (string)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
    }

    public function includeRoles(User $user)
    {
        return $this->collection($user->getRoles(), new RoleTransformer);
    }

}