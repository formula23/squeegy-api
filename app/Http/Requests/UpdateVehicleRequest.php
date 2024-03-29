<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Vehicle;

class UpdateVehicleRequest extends Request {

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
            'year' => 'sometimes|required|digits:4',
            'make' => 'sometimes|required',
            'model' => 'sometimes|required',
            'color' => 'sometimes|required',
//            'license_plate' => 'sometimes|required',
        ];
	}

}
