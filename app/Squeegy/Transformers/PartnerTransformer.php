<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 5/5/16
 * Time: 23:33
 */

namespace App\Squeegy\Transformers;


use App\Partner;
use App\Squeegy\Schedule;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class PartnerTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'services',
        'instructions',
    ];

    protected $availableIncludes = [
        'availability',
        'days',
    ];
    
    public function transform(Partner $partner)
    {
        return [
            'id'=>(string)$partner->id,
            'code'=>$partner->code,
            'name'=>$partner->name,
            'location_name'=>$partner->name,
            'location'=>$partner->location,
            'description'=>($partner->description?:trans('messages.order.corp_no_description')),
        ];
    }

    public function includeAvailability($partner)
    {
        $avail = (new Schedule(null, $partner->id))->availability();

        return $this->item($avail, new PartnerAvailabilityTransformer());
    }

    public function includeDays(Partner $partner)
    {
        return $this->collection($partner->days, new PartnerDayTransformer());
    }

    public function includeServices(Partner $partner, ParamBag $paramBag = null)
    {
        $services = $partner->services();

        if($paramBag) {
            list($orderCol, $orderBy) = $paramBag->get('order');
            if($orderCol && $orderBy) {
                $services->orderBy($orderCol, $orderBy);
            }
        }

        return $this->collection($services->get(), new ServiceTransformer('corp'));
    }

    public function includeInstructions(Partner $partner, ParamBag $paramBag = null)
    {
        $instructions_builder = $partner->instructions(true);

        if($paramBag) {
            list($is_active,) = $paramBag->get('is_active');
            if( ! is_null($is_active)) $instructions_builder->wherePivot('is_active', $is_active);
        }

        return $this->collection($instructions_builder->get(), new InstructionTransformer());
    }

}