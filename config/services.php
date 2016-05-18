<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],

	'mandrill' => [
		'secret' => env('MANDRILL_SECRET', ''),
	],

	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],

	'stripe' => [
		'model'  => 'App\User',
		'secret' => env('STRIPE_API_KEY', ''),
        'supply' => [
            'model'  => 'App\User',
            'secret' => env('STRIPE_SUPPLY_API_KEY', ''),
        ]
	],
	
	'mixpanel' => [
		'api_key' => env('MIXPANEL_API_KEY'),
		'token' => env('MIXPANEL_TOKEN'),
	],

];
