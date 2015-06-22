<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateUserRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'first_name' => 'sometimes|required|min:2',
            'last_name' => 'required|sometimes|min:2',
            'email' => 'sometimes|required|email|unique:users,email,'.$this->user()->id,
            'phone' => 'sometimes|required|digits:10',
            'push_token' => 'sometimes|required',
            'facebook_id' => 'sometimes|required',
		];
	}

}
