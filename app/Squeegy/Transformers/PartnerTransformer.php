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
        'availability',
        'services',
    ];

    protected $availableIncludes = [
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
            'description'=>$partner->description,
        ];
    }

    public function includeAvailability($partner)
    {
        $avail = (new Schedule(null, $partner->id))->availability();

        return $this->item($avail, new PartnerAvailabilityTransformer());
    }

    public function includeDays(Partner $partner)
    {
//        dd($partner->days);

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

        return $this->collection($services->get(), new ServiceTransformer());
    }
    
}