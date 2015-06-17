<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 00:28
 */

namespace App\OctaneLA\Transformers;


use App\ServiceCoords;
use League\Fractal\TransformerAbstract;

class ServiceCoordTransformer extends TransformerAbstract {

    public function transform(ServiceCoords $service_coords)
    {
        return [$service_coords->lat, $service_coords->lng];
    }

}