<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 5/5/16
 * Time: 18:53
 */

namespace App\Squeegy;


use App\Partner;
use GeometryLibrary\PolyUtil;

class Partners
{

    public function is_partner($lat, $lng)
    {
        
        $partners = Partner::all();
        
        foreach($partners as $partner) {

            $resp = PolyUtil::containsLocation(
                ['lat' => $lat, 'lng' => $lng], // point array [lat, lng]
                $this->format_coords($partner->geo_fence)
            );

            if($resp) {
                return true;
            }
        }
        
        return false;
    }
    
    private function format_coords($geo_fence)
    {
        $polygon=[];

        foreach($geo_fence as $coord) {
            $polygon[] = ['lat'=>$coord->lat, 'lng'=>$coord->lng];
        }

        return $polygon;
    }
    
}