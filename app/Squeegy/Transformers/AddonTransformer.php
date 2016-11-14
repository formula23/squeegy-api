<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 10/14/16
 * Time: 16:44
 */

namespace App\Squeegy\Transformers;


use App\Addon;

class AddonTransformer
{

    public function transform(Addon $addon) {

        if( ! $addon->exists) return [];
        return [
            'id'=>$addon->id,
            'name'=>$addon->name,
            'description'=>$addon->description,
            'price'=>$addon->price,
            'sequence'=>(!empty($addon->pivot->sequence)?$addon->pivot->sequence:0),
        ];

    }


}