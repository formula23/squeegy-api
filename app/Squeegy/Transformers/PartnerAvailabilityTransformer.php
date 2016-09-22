<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/20/16
 * Time: 20:50
 */

namespace App\Squeegy\Transformers;


use League\Fractal\TransformerAbstract;

class PartnerAvailabilityTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'available_days',
    ];

    public function transform($availability)
    {
        $not_accepting_msg='';
        if($availability['next_day']) {
            $not_accepting_msg = trans('messages.order.not_accepting_next_day',[
                'date'=>$availability['next_day']->format('l, F d g:ia'),
            ]);
        }

        return [
            "not_accepting_msg"=>$not_accepting_msg,
        ];
    }

    public function includeAvailableDays($availability)
    {
        return $this->collection($availability['available_days'], new PartnerAvailableDaysTransformer());
    }

}