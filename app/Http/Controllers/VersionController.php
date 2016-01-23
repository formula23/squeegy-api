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

            $this->parseVersionNumber($user_app_version);

            $android = ($request->header('X-Device') == "Android" ? "_ANDROID" : "" );
            $app_type_key = ($user_app_type=="washer" ? "WASHER_" : "" );

            $install_link = env($app_type_key.'APP_INSTALL'.$android);
            $min_app_version = env($app_type_key."APP_VERSION".$android);

            $this->parseVersionNumber($min_app_version, "min_app_version");

            $this->requireUpgrade();

            if($this->upgrade && $install_link) {
                return $this->response->withArray(['status'=>'upgrade', 'install_link'=>$install_link]);
            } else {
                return $this->response->withArray(["status"=>"ok"]);
            }
        }
    }

    /**
     * @param $min_ver_number
     * @param string $type
     * @internal param $version_numer
     */
    public function parseVersionNumber($min_ver_number, $type="user_app_version")
    {
        preg_match("/([1-9]+)\.([0-9]+)\.*([0-9]+)*/", $min_ver_number, $req_match);
        $this->{$type}[] = (int)$req_match[1];
        $this->{$type}[] = (int)$req_match[2];
        $this->{$type}[] = (int)(isset($req_match[3]) ? $req_match[3] : 0);

    }

    /**
     *
     */
    public function requireUpgrade()
    {
        for($i=0;$i<=2;$i++) {
            if($this->user_app_version[$i] < $this->min_app_version[$i]) { $this->upgrade = true; break; }
        }
    }

}
