<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Order;
use App\User;
use Illuminate\Support\Facades\Validator;

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
        Validator::extend('unique_email', function ($field, $value, $parameters) {

            $old_users = User::where($field, $value)->where('id', '!=', $parameters[2])->get();
            if( ! $old_users->count() ) return true;

            $current_user = User::find($parameters[2]);

            if($old_users->count() && preg_match('/squeegyapp-tmp\.com$/', $current_user->email)) {
                $old_user = $old_users->first();

                if($old_user->phone == "+1".Request::input('phone')) { //update old account and orders and return true
                    $old_user->email = "old-".str_random(5)."-".$old_user->email;
                    $old_user->save();

                    //update orders
                    Order::where('user_id', $old_user->id)->update(['user_id'=>$current_user->id]);
                    return true;
                }
            }
            return false;
        });

		return [
            'name' => 'sometimes|required|min:2',
            'email' => 'sometimes|required|email|unique_email:users,email,'.$this->user()->id,
//            'email' => 'sometimes|required|email|unique:users,email,'.$this->user()->id,
            'phone' => 'sometimes|required|digits:10',
            'push_token' => 'sometimes|required',
            'facebook_id' => 'sometimes|required',
		];
	}

    public function messages()
    {
        return [
            'unique_email' => 'This email address is already in use.',
        ];
    }
}

