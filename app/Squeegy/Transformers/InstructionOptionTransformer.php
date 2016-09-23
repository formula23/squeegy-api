<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/23/16
 * Time: 10:41
 */

namespace app\Squeegy\Transformers;


use App\InstructionOption;
use League\Fractal\TransformerAbstract;

class InstructionOptionTransformer extends TransformerAbstract
{

    public function transform(InstructionOption $instructionOption) {
        return [
            'id'=>$instructionOption->id,
            'option'=>$instructionOption->option,
            'value'=>$instructionOption->value,
        ];
    }

}