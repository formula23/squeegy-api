<?php namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Squeegy\PushNotification;

class PushNotif extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'push:notif';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Push Notification';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $default_users = User::select(['id','push_token'])->where('email', 'dan@formula23.com')->orWhere('email', 'sinisterindustries@yahoo.com')->get();

        $default_users_arr = [];
        foreach($default_users as $def_user) {
            $default_users_arr[] = $def_user;
        }

//        $message = "If you missed black friday. We've extended it, use promo code: BLACKFRIDAY to get 50% off your car wash.";
//        $message = "Get your car cleaned on cyber monday for 40% off with code CYBER40";
        $message = "Don't forget, your first car wash is on us. Request now! - Use Promo Code FREE";
//            \DB::connection()->enableQueryLog();

//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
//            ->whereNotIn('id', function($q) {
//                $q->select('user_id')
//                    ->from('orders')
//                    ->where('status', 'done')
//                    ->where('confirm_at', '>', '2015-11-26')
//                    ->orWhere(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-11-30');
//            })
//            ->take(2000)
//            ->skip(2000)
//            ->get();
//            $queries = \DB::getQueryLog();
//            print_r($queries);

//        dd($users);



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
            $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
                ->where('email', 'like', '%squeegyapp-tmp.com%')
                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '>', '2015-11-26')
                ->orderBy('id');

            $user_qry = User::select(['id', 'push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
                ->where('email', 'like', '%squeegyapp-tmp.com')
                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '>', '2015-11-26')
                ->orderBy('id');

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
            $users = $user_qry->get();

//        print_r($default_users->toArray());
// dd($users->toArray());
        $send_list = array_merge($users, $default_users_arr);

        $this->info("user count:".count($send_list));
        $this->info("sent message:");

 dd($send_list);
        foreach($send_list as $user) {
// dd($user);
            if(empty($user->push_token)) continue;

          PushNotification::send($user->push_token, $message, 1);
            $this->info("sent to: id# ".$user->id." - ".$user->push_token);
//            print "sent to: id# ".$user->id." - ".$user->push_token."\n";
        }

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
//			['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
//			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
