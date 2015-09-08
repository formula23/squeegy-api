<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Squeegy\PushNotification;
use App\User;
use Illuminate\Http\Request;

class NotifyController extends Controller {

	public function push(Request $request)
    {

        $users = User::where('app_version', '>=', '1.3')->where('is_active', 1)->whereNotNull('push_token')->get();

        foreach($users as $user) {
            PushNotification::send($user->push_token, $request->input('message'), 0);
        }

    }

}
