<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Squeegy\PushNotification;
use App\User;
use Illuminate\Http\Request;

class NotifyController extends Controller {

	public function push(Request $request)
    {
        set_time_limit(0);

//        if(!$request->input('user_id')) return;

        $default_users = User::where('email', 'dan@formula23.com')->orWhere('email', 'sinisterindustries@yahoo.com')->get();

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

//            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')->where('email', 'like', '%squeegyapp-tmp.com%')
//                ->where('id', '>=', 450)
//                ->where('created_at', '<', '2015-09-28')
//                ->orderBy('id');
//
            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')
                ->where('email', 'like', '%squeegyapp-tmp.com%')
                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-10-19')
                ->orderBy('id');

//            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')
//                ->where('email', 'not like', '%squeegyapp-tmp.com%')
//                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '<', '2015-09-25')
//                ->orderBy('id');

//            $user_qry = User::leftJoin('orders', 'users.id', '=', 'orders.user_id')
//                ->where('app_version', '1.4')->where('push_token', '!=', '')
//                ->where(\DB::raw('DATE_FORMAT(users.created_at, \'%Y-%m-%d\')'), '<', '2015-10-02')
//                ->where('orders.status', 'done')
//                ->orWhereNull('orders.status')
//                ->where(\DB::raw('DATE_FORMAT(orders.confirm_at, \'%Y-%m-%d\')'), '<', '2015-09-30')
//                ->orWhereNull('orders.confirm_at')
//                ->whereIn('users.id', [14,15,19,21,24], 'and', true)
//                ->groupBy('users.id')
//                ->orderBy('users.id')
//                ->skip(184)
//                ->take(184);

//            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')
//                ->where('email', 'like', '%squeegyapp-tmp.com%');
//
//
//            $user_qry = User::where('app_version', '1.3')->where('push_token', '!=', '')
//                ->where('email', 'like', '%squeegyapp-tmp.com%');
//
//
//            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')
//                ->where('email', 'like', '%squeegyapp-tmp.com%')
//                ->where('created_at', '>=', '2015-09-26');
//dd($user_qry->toSql());

//            $user_qry = User::join('orders', 'users.id', '=', 'orders.user_id')
//                ->where('app_version', '>=', '1.3')
//                ->where('users.is_active', 1)
//                ->whereNotNull('push_token')
//                ->where('push_token', '!=', '')
//                ->where('email', 'not like', '%squeegyapp-tmp.com%')
//                ->whereIn('orders.status', ['done'])
//                ->where('orders.done_at', '<', '2015-09-16')
//                ->groupBy('users.id');
            $users = $user_qry->get();
        }
//dd($users);
        $send_list = array_merge($users->toArray(), $default_users->toArray());

        print "user count:".count($send_list)."\n";
        print "sent message:\n\n";
        print $request->input('message')."\n\n";

        foreach($send_list as $user) {

            if(empty($user['push_token'])) continue;

            PushNotification::send($user['push_token'], $request->input('message'), 1);

            print "sent to: id# ".$user['id']." - ".$user['push_token']."\n";
        }

    }

}
