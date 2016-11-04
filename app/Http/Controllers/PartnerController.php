<?php

namespace App\Http\Controllers;

use App\Partner;
use App\Squeegy\Schedule;
use App\Squeegy\Transformers\PartnerTransformer;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;


class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::all();
        return $this->response->withCollection($partners, new PartnerTransformer());
    }

    public function show(Request $request, Partner $partner=null)
    {
        if( ! $partner->exists || ! $partner->is_active) {
            return $this->response->errorWrongArgs('Invalid Code. Try Again.');
        }
        return $this->response->withItem($partner, new PartnerTransformer());

    }
}
