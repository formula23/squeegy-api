<?php

namespace App\Http\Controllers;

use App\Partner;
use App\Squeegy\Transformers\PartnerTransformer;
use Illuminate\Http\Request;
use App\Http\Requests;


class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::all();
        return $this->response->withCollection($partners, new PartnerTransformer());
    }
}
