<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/15/15
 * Time: 10:56
 */

namespace App\OctaneLA\Transformers;

use App\Washer;
use League\Fractal\TransformerAbstract;

/**
 * Class WasherTransformer
 * @package App\OctaneLA\Transformers
 */
class WasherTransformer extends TransformerAbstract {

    /**
     * @param Washer $washer
     * @return array
     */
    public function transform(Washer $washer)
    {
        return [
            'id' => $washer->id,
            'name' => $washer->name,
            'phone' => $washer->phone,
        ];
    }

} 