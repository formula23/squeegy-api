<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class VersionController extends Controller {

    public function index()
    {
        return $this->response->withArray(['version'=>env('APP_VERSION')]);
    }

}
