<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 14:03
 */

namespace App\OctaneLA\Transformers;


use League\Fractal\TransformerAbstract;

class ServiceAvailabilityTransformer extends TransformerAbstract {

    public function transform(array $data) {

        return [
            'open' => $data['open'],
            'time' => $data['time'],
            'max' => $data['max'],
        ];

    }

}