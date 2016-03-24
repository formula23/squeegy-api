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

    /**
     * WashersController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if($request->header('Authorization')) {
            $this->middleware('jwt.auth');
        } else {
            $this->middleware('auth', ['only' => 'dutyStatus']);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function locations(Request $request)
    {
//        $washer_locations_qry = WasherLocation::whereDate('washer_locations.updated_at', '=', Carbon::today()->toDateString());
        $washer_locations_qry = WasherLocation::query();

        if($request->input('status') == 'onduty') {
            $washer_locations_qry->join('washer_activity_logs', 'washer_locations.user_id' ,'=', 'washer_activity_logs.user_id')
                ->whereNotNull('washer_activity_logs.login')
                ->whereNull('washer_activity_logs.logout')
                ->groupby('washer_locations.user_id');
        }

        $washer_locations = $washer_locations_qry->get();
//dd($washer_locations);
        return $this->response->withCollection($washer_locations, new WasherLocationTransformer());
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function dutyStatus()
    {
        $latest_duty_status = Auth::user()->activity_logs()->orderBy('updated_at', 'desc')->first();
//dd($latest_duty_status->toArray());
        return $this->response->withArray([
            'status'=>($latest_duty_status->log_off || !$latest_duty_status->log_on ? "off" : "on" ),
            'login'=>$latest_duty_status->login,
            'logout'=>$latest_duty_status->logout,
            'log_on'=>$latest_duty_status->log_on,
            'log_off'=>$latest_duty_status->log_off]);

    }

}