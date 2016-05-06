<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 5/5/16
 * Time: 23:33
 */

namespace App\Squeegy\Transformers;


use App\Partner;
use League\Fractal\TransformerAbstract;

class PartnerTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'days',
        'services',
    ];

    public function transform(Partner $partner)
    {
        return [
            'id'=>(string)$partner->id,
            'name'=>$partner->name,
            'location'=>$partner->location,
        ];
    }
    
    public function includeDays(Partner $partner)
    {
        return $this->collection($partner->days, new PartnerDayTransformer());
    }

    public function includeServices(Partner $partner)
    {
        return $this->collection($partner->services, new ServiceTransformer());
    }
    
}