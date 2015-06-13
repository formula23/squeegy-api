<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/3/15
 * Time: 10:56
 */

namespace App\OctaneLA\Transformers;


use App\Vehicle;
use League\Fractal\TransformerAbstract;

/**
 * Class VehicleTransformer
 * @package App\OctaneLA\Transformers
 */
class VehicleTransformer extends TransformerAbstract {


    /**
     * @param Vehicle $vehicle
     * @return array
     */
    public function transform(Vehicle $vehicle)
    {
        return [
            'id' => $vehicle->id,
            'year' => $vehicle->year,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'color' => $vehicle->color,
            'type' => $vehicle->type,
            'license_plate' => $vehicle->license_plate,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('api.v1.vehicles.show', ['vehicles'=>$vehicle->id])
                ]
            ],
        ];
    }

} 