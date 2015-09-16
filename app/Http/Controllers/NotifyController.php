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

        if($request->input('user_id')) {
            $users = User::whereIn('id', explode(",",$request->input('user_id')))->get();

        } else {
            //anonymous users
//            $users = User::where('app_version', '>=', '1.3')
//                ->where('is_active', 1)
//                ->whereNotNull('push_token')
//                ->where('push_token', '!=', '')
//                ->where('email', 'like', '%squeegyapp-tmp.com%')
//                ->get();
            $user_qry = User::join('orders', 'users.id', '=', 'orders.user_id')
                ->where('app_version', '>=', '1.3')
                ->where('users.is_active', 1)
                ->whereNotNull('push_token')
                ->where('push_token', '!=', '')
                ->where('email', 'not like', '%squeegyapp-tmp.com%')
                ->whereIn('orders.status', ['done'])
                ->where('orders.done_at', '<', '2015-09-16')
                ->groupBy('users.id');
            $users = $user_qry->get();
        }
        
        print "user count:".$users->count()."\n";
        print "sent message:\n\n";
        print $request->input('message')."\n\n";

        foreach($users as $user) {
            if(empty($user->push_token)) continue;

            PushNotification::send($user->push_token, $request->input('message'), 1);

            print "sent to: ".$user->push_token."\n";
        }

    }

}
