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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WashersController extends Controller {

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth', ['only' => 'dutyStatus']);
    }

    public function locations(Request $request)
    {
        $washer_locations_qry = WasherLocation::whereDate('washer_locations.updated_at', '=', Carbon::today()->toDateString());

        if($request->input('status') == 'onduty') {
            $washer_locations_qry->join('washer_activity_logs', 'washer_locations.user_id' ,'=', 'washer_activity_logs.user_id')
                ->whereNull('washer_activity_logs.log_off');
        }

        $washer_locations = $washer_locations_qry->get();

        return $this->response->withCollection($washer_locations, new WasherLocationTransformer());
    }

    public function dutyStatus()
    {
        $latest_duty_status = Auth::user()->activity_logs()->orderBy('updated_at', 'desc')->first();

        return $this->response->withArray(['status'=>($latest_duty_status->log_off ? "off" : "on" ), 'log_on'=>$latest_duty_status->log_on, 'log_off'=>$latest_duty_status->log_off]);

    }

}