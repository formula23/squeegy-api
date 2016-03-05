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
				'body' => 'Give a friend $10 credit towards their first car wash and earn a $10 credit yourself.',
				'share_msg' => 'Hey, I use Squeegy to wash my car on-demand and want to send you $10 to try it. Use my referral code:',
				'share_link' => 'Download the app here: https://www.squeegyapp.com/free-washes/',
			],
			'create_password' => [
				'header' => 'Create a Password',
				'body' => 'We\'ve added many new features to this version of Squeegy. To continue, please add a password to your account.',
			],
		];
		return $this->response->withArray($data);
	}

}
