<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\Squeegy\Transformers;

use App\Service;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'addons'
    ];

    protected $scope;

    public function __construct($scope=null)
    {
        $this->scope = $scope;
    }

    public function transform(Service $service)
    {
        return [
            'id' => (string)$service->id,
            'name' => $service->name,
            'price' => (string)$service->price(),
            'details' => $service->details,
            'time' => $service->time,
            'time_label' => $service->time_label,
            'sequence' => $service->sequence,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('v1.services.show', ['service'=>$service->id])
                ]
            ],
        ];
    }

    protected function includeAddons(Service $service)
    {
        return $this->collection($service
            ->addons()
            ->wherePivot('is_active', 1)
            ->orderBy('sequence')
            ->wherePivot('is_corp', (($this->scope=='corp')?:0))
            ->get(), new AddonTransformer());

    }

}
