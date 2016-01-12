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
        $user_app_type = $request->input('app_type');
        if($user_app_version) {

            $android = ($request->header('X-Device') == "Android" ? "_ANDROID" : "" );

            if($user_app_type=="washer") {

                $min_app_version = (float)env("WASHER_APP_VERSION".$android);
                $install_link = env('WASHER_APP_INSTALL'.$android);

                if($user_app_version < $min_app_version && $install_link) {
                    return $this->response->withArray(['status'=>'upgrade', 'install_link'=>$install_link]);
                } else {
                    return $this->response->withArray(["status"=>"ok"]);
                }

            } else {
                $min_app_version = (float)env("APP_VERSION".$android);

                if($user_app_version < $min_app_version) {
                    return $this->response->withArray(["status"=>"upgrade"]);
                } else {
                    return $this->response->withArray(["status"=>"ok"]);
                }
            }
        }
    }

}
