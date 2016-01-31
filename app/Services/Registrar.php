<?php namespace App\Services;

use App\User;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Registrar implements RegistrarContract {

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(array $data)
	{
		return Validator::make($data, [
//			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|min:8',
//            'phone' => 'required|digits:10',
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create([
			'name' => ! empty($data['name']) ? $data['name'] : '',
			'email' => $data['email'],
			'password' => $data['password'],
            'phone' => ! empty($data['phone']) ? $data['phone'] : '',
            'stripe_customer_id' => (isset($data['stripe_customer_id']) ? $data['stripe_customer_id']:null),
            'push_token' => ! empty($data['push_token']) ? $data['push_token'] : '',
			'referral_code' => $data['referral_code'],
		]);
	}
}
