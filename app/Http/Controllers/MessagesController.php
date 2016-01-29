<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class MessagesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$data=[
			'referral_program' => [
				'header' => 'Invite friends. Get free washes.',
				'body' => 'Send friends a free car wash and you\'ll get one to, worth $15.',
			],
			'create_password' => [
				'header' => 'Create a Password',
				'body' => 'We\'ve added many new features to this version of Squeegy. To continue, please add a password to your account.',
			],
		];
		return $this->response->withArray($data);
	}

}
