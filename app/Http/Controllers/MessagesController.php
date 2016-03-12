<?php


namespace App\Http\Controllers;

use App\Http\Requests;


class MessagesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		return $this->response->withArray(trans('messages.app_copy'));
	}

}
