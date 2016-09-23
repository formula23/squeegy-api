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
//        dd($instruction->toArray());
        return [
            'key'=>$instruction->key,
            'label'=>($instruction->pivot->label?:$instruction->label),
            'hint'=>($instruction->pivot->hint?:$instruction->hint),
            'type'=>$instruction->type,
            'input_type'=>$instruction->input_type,
            'prepopulate'=>($instruction->pivot->prepopulate?:$instruction->prepopulate),
            'required'=>($instruction->pivot->required?:$instruction->required),
            'min_length'=>($instruction->pivot->min_length?:$instruction->min_length),
            'max_length'=>($instruction->pivot->max_length?:$instruction->max_length),
            'validation'=>($instruction->pivot->validation?:$instruction->validation),
            'validation_error_msg'=>($instruction->pivot->validation_error_msg?:$instruction->validation_error_msg),
        ];
    }
    
}