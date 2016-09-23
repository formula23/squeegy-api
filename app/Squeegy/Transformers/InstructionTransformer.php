<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/22/16
 * Time: 20:32
 */

namespace app\Squeegy\Transformers;


use App\Instruction;
use League\Fractal\TransformerAbstract;

class InstructionTransformer extends TransformerAbstract
{

    public function transform(Instruction $instruction)
    {
        return [
            'id'=>$instruction->id,
            'label'=>($instruction->pivot->label?:$instruction->label),
            'hint'=>($instruction->pivot->hint?:$instruction->hint),
            'type'=>$instruction->type,
            'input_type'=>$instruction->input_type,
            'prepopulate'=>( ! is_null($instruction->pivot->prepopulate) ? $instruction->pivot->prepopulate : $instruction->prepopulate),
            'required'=>( ! is_null($instruction->pivot->required) ? $instruction->pivot->required : $instruction->required),
            'min_length'=>($instruction->pivot->min_length?:$instruction->min_length),
            'max_length'=>($instruction->pivot->max_length?:$instruction->max_length),
            'validation'=>( ! is_null($instruction->pivot->validation) ? $instruction->pivot->validation : $instruction->validation),
            'validation_error_msg'=>($instruction->pivot->validation_error_msg?:$instruction->validation_error_msg),
        ];
    }
    
}