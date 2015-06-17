<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 00:28
 */

namespace App\OctaneLA\Transformers;


use App\ServiceCoord;
use League\Fractal\TransformerAbstract;

class ServiceCoordTransformer extends TransformerAbstract {

    public function transform(ServiceCoord $service_coord)
    {
        return [$service_coord->lat, $service_coord->lng];
    }

}