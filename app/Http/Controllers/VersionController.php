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

                $min_app_version = env("WASHER_APP_VERSION".$android);
                preg_match("/([1-9]+)\.([0-9]+)\.*([0-9]+)*/", $min_app_version, $req_match);

                $required_major = $req_match[1];
                $required_minor = $req_match[2];
                $required_build = (isset($req_match[3]) ? $req_match[3] : 0);

                preg_match("/([1-9]+)\.([0-9]+)\.*([0-9]+)*/", $user_app_version, $app_match);
                $major = $app_match[1];
                $minor = $app_match[2];
                $build = (isset($app_match[3]) ? $app_match[3] : 0);

                $upgrade=false;
                if($major < $required_major) { $upgrade = true; }
                elseif($minor < $required_minor) { $upgrade = true; }
                elseif($build < $required_build) { $upgrade = true; }

                $install_link = env('WASHER_APP_INSTALL'.$android);

                if($upgrade && $install_link) {
                    return $this->response->withArray(['status'=>'upgrade', 'install_link'=>$install_link]);
                } else {
                    return $this->response->withArray(["status"=>"ok"]);
                }

            } else {
                $min_app_version = (float)env("APP_VERSION".$android);

                if((float)$user_app_version < $min_app_version) {
                    return $this->response->withArray(["status"=>"upgrade"]);
                } else {
                    return $this->response->withArray(["status"=>"ok"]);
                }
            }
        }
    }

}
