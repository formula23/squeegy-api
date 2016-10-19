<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 10/14/16
 * Time: 16:44
 */

namespace app\Squeegy\Transformers;


use App\Addon;

class AddonsTransformer
{

    public function transform(Addon $addon) {

        return [
            'id'=>$addon->id,
            'name'=>$addon->name,
            'description'=>$addon->description,
            'price'=>$addon->price,
            'sequence'=>$addon->pivot->sequence,
        ];

    }


}