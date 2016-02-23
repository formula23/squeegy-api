<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/3/15
 * Time: 10:56
 */

namespace App\Squeegy\Transformers;


use App\Vehicle;
use League\Fractal\TransformerAbstract;

/**
 * Class VehicleTransformer
 * @package App\Squeegy\Transformers
 */
class VehicleTransformer extends TransformerAbstract {


    /**
     * @param Vehicle $vehicle
     * @return array
     */
    public function transform(Vehicle $vehicle)
    {
        return [
            'id' => (string)$vehicle->id,
            'year' => $vehicle->year,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'color' => $vehicle->color,
            'type' => $vehicle->type,
            'size' => $vehicle->size,
            'license_plate' => $vehicle->license_plate,
            'removed' => $vehicle->trashed(),
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('v1.vehicles.show', ['vehicles'=>$vehicle->id])
                ]
            ],
        ];
    }

} 