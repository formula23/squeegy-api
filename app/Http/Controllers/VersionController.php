<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class VersionController extends Controller {

    protected $min_app_version=[];
    protected $user_app_version=[];
    protected $upgrade=false;

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function index()
    {
        return $this->response->withArray(['version'=>env('APP_VERSION')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function check(Request $request)
    {
        $user_app_version = $request->header('X-Application-Version');
        $user_app_type = strtolower($request->header('X-Application-Type'));

        if($user_app_version) {

            $android = ($request->header('X-Device') == "Android" ? "_ANDROID" : "" );
            $app_type_key = ($user_app_type=="washer" ? "WASHER_" : "" );

            $install_link = env($app_type_key.'APP_INSTALL'.$android);
            $min_app_version = env($app_type_key."APP_VERSION".$android);
            
            $this->upgrade = version_compare($min_app_version, $user_app_version, '>');
            
            if($this->upgrade && $install_link) {
                return $this->response->withArray(['status'=>'upgrade', 'install_link'=>$install_link]);
            } else {
                return $this->response->withArray(["status"=>"ok"]);
            }
        }
    }
}
