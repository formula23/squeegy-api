<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MessagesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$ref_prog_amts = Config::get('squeegy.referral_program');

		$data=[
			'referral_program' => [
				'header' => 'Invite friends. Get free washes.',
				'body' => 'Give a friend $'.($ref_prog_amts['referred_amt']/100).' credit towards their first car wash and earn a $'.($ref_prog_amts['referrer_amt']/100).' credit yourself.',
				'share_msg' => 'Hey, I use Squeegy to wash my car on-demand and want to send you $'.($ref_prog_amts['referred_amt']/100).' to try it. Use my referral code:',
				'share_link' => 'Download the app here: https://www.squeegyapp.com/free-washes/',
				'email_subject' => 'Get $'.($ref_prog_amts['referred_amt']/100).' off your first car wash using Squeegy on-demand car wash!',
			],
			'create_password' => [
				'header' => 'Create a Password',
				'body' => 'We\'ve added many new features to this version of Squeegy. To continue, please add a password to your account.',
			],
		];
		return $this->response->withArray($data);
	}

}
