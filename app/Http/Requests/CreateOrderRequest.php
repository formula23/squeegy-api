<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateOrderRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
        return \Auth::user()->is('customer');
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'service_id' => 'gt:0',
            'vehicle_id' => 'gt:0',
            'location' => 'required',
		];

	}

    public function messages()
    {
        return [
            'service_id.gt' => 'Service Id required',
            'vehicle_id.gt' => 'Vehicle Id required',
        ];
    }

}
