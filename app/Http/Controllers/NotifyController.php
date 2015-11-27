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

        $default_users = User::select(['id','push_token'])->where('email', 'dan@formula23.com')->orWhere('email', 'sinisterindustries@yahoo.com')->get();

        if($request->input('user_id')) {
            $users = User::whereIn('id', explode(",",$request->input('user_id')))->get();
        } else {

//            \DB::connection()->enableQueryLog();

            $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
                    ->whereNotIn('id', function($q) {
                        $q->select('user_id')
                            ->from('orders')
                            ->where('status', 'done')
                            ->where('confirm_at', '>', '2015-11-26')
                            ->orWhere(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-11-27');
                })
                ->get();
//            $queries = \DB::getQueryLog();
//            print_r($queries);

//            dd($users);



//            dd($queries);


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
//            $user_qry = User::select(['id', 'push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
//                ->where('email', 'like', '%squeegyapp-tmp.com%')
//                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-11-26')
//                ->orderBy('id');

//            $user_qry = User::where('app_version', '1.4')->where('push_token', '!=', '')
//                ->whereIn('id', [161,260,406,454,521,436,170,531,390,287,463,781,997,898,1025,1067,2288,1080,1039,1153,1174,1178,1127,1202,1177,1301,1289,1423,1306,1306,1489,1500,1576,1549,1518,1622,1679,1750,1615,1389,1034,1060,1856,1800,1893,1284,1507,1109]);

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
//            $users = $user_qry->get();
        }
//        print_r($default_users->toArray());
//dd($users);
        $send_list = array_merge($users, $default_users->toArray());

        print "user count:".count($send_list)."\n";
        print "sent message:\n\n";
        print $request->input('message')."\n\n";
dd($send_list);
        foreach($send_list as $user) {

            if(empty($user['push_token'])) continue;

//            PushNotification::send($user['push_token'], $request->input('message'), 1);

            print "sent to: id# ".$user['id']." - ".$user['push_token']."\n";
        }

    }

}
