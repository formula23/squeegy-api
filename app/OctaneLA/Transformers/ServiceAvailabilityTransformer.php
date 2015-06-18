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
            'accept' => (int)$data['accept'],
            'description' => $data['description'],
            'time' => $data['time'],
            'time_label' => $data['time_label'],
        ];

    }

}