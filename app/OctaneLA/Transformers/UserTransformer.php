<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/9/15
 * Time: 16:15
 */

namespace App\OctaneLA\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract {

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'photo' => $user->photo,
            'stripe_id' => $user->stripe_id,
            'push_token' => $user->push_token,
            'facebook_id' => $user->facebook_id,
        ];
    }

}