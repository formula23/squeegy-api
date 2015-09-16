<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Squeegy\PushNotification;
use App\User;
use Illuminate\Http\Request;

class NotifyController extends Controller {

	public function push(Request $request)
    {
//        if(!$request->input('user_id')) return;
        
        //anonymous users
        $users = User::where('app_version', '>=', '1.3')
            ->where('is_active', 1)
            ->whereNotNull('push_token')
            ->where('push_token', '!=', '')
            ->where('email', 'like', '%squeegyapp-tmp.com%')
            ->get();

//        $users = User::whereIn('id', explode(",",$request->input('user_id')))->get();

        foreach($users as $user) {
            if(empty($user->push_token)) continue;
            PushNotification::send($user->push_token, $request->input('message'), 0);
        }

    }

}
