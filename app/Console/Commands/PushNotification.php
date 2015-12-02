<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushNotification extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'push:notification';

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
        $default_users = \DB::table('users')->select(['id','push_token'])->where('email', 'dan@formula23.com')->orWhere('email', 'sinisterindustries@yahoo.com')->get();

//        $message = "If you missed black friday. We've extended it, use promo code: BLACKFRIDAY to get 50% off your car wash.";
//        $message = "Get your car cleaned on cyber monday for 40% off with code CYBER40";
        $message = "Don't forget, your first car wash is on us. Request now! - Use Promo Code: FREE";

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

            $user_qry = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
                ->where('email', 'like', '%squeegyapp-tmp.com%')
                ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-12-01')
                ->orderBy('id');

            $users = $user_qry->get();

        //create topic
        $push_client = \App::make('Aws\Sns\SnsClient');

        $resp = $push_client->CreateTopic(['Name' => 'Daily Push']);
        $topic_arn = $resp->get('TopicArn');

//        print_r($default_users->toArray());
// dd($users->toArray());
        $send_list = array_merge($users, $default_users);

        $this->info("user count: ".count($send_list));


        foreach($send_list as $user) {
            if(empty($user->push_token)) continue;

            $push_client->Subscribe([
                'TopicArn' => $topic_arn,
                'Protocol' => 'application',
                'Endpoint' => $user->push_token,
            ]);

            $this->info('user id subscribed: ' . $user->id);
        }

        $this->info("publish message: ".$message);

        $aps_payload = [
            'aps' => [
                'alert' => $message,
                'sound' => 'default',
                'badge' => 1
            ],
        ];

        $message = json_encode([
            'default' => $message,
            env('APNS') => json_encode($aps_payload)
        ]);

        $push_client->publish([
            'TargetArn' => $topic_arn,
            'MessageStructure' => 'json',
            'Message' => $message,
        ]);

        $this->info("Delete Topic");

        $push_client->deleteTopic([
            'TopicArn' => $topic_arn,
        ]);

        $this->info("Done!");

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
