<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 12/23/15
 * Time: 20:39
 */

namespace App\Http\Controllers;


use App\Squeegy\Transformers\WasherLocationTransformer;
use App\WasherLocation;
use Carbon\Carbon;

class WashersController extends Controller {

    public function locations()
    {
        $washer_locations = WasherLocation::whereDate('updated_at', '=', Carbon::today()->toDateString())->get();

        return $this->response->withCollection($washer_locations, new WasherLocationTransformer());
    }



}