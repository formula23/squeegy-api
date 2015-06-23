<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class UpdateOrderRequest extends Request {

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
            'washer_id' => 'required|sometimes',
            'location' => 'required|sometimes',
            'status' => 'required|sometimes',
            'en_route_at' => 'required|sometimes',
            'start_at' => 'required|sometimes',
            'done_at' => 'required|sometimes',
            'photo_count' => 'required|sometimes',
            'discount_code' => 'required|sometimes',
            'rating' => 'required|sometimes',
            'rating_comment' => 'required|sometimes',
		];
	}

}
