<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class VersionController extends Controller {

    public function index()
    {
        return $this->response->withArray(['version'=>env('APP_VERSION')]);
    }

    public function check(Request $request)
    {
        $user_app_version = $request->input('app_version');
        if($user_app_version) {
            $version = "APP_VERSION".($request->header('X-Device') == "Android" ? "_ANDROID" : "" );
            $min_app_version = (float)env($version);
            if($user_app_version < $min_app_version) {
                return $this->response->withArray(["status"=>"upgrade"]);
            } else {
                return $this->response->withArray(["status"=>"ok"]);
            }
        }
    }

}
