<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 5/11/16
 * Time: 17:39
 */

namespace app\Squeegy\Transformers;


use App\Zone;
use League\Fractal\TransformerAbstract;

class ZoneTransformer extends TransformerAbstract
{
    public function transform(Zone $zone)
    {
        return [
            'id'=>$zone->id,
            'name'=>$zone->name,
            'code'=>$zone->code,
        ];
    }
    
}