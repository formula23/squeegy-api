<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/3/15
 * Time: 10:56
 */

namespace App\OctaneLA\Transformers;


class VehicleTransformer extends Transformer {


    public function transform($vehicle)
    {
        return [
            'year' => $vehicle['year'],
            'make' => $vehicle['make'],
            'color' => $vehicle['color'],
            'type' => $vehicle['type'],
            'license_plate' => $vehicle['license_plate']
        ];
    }

} 