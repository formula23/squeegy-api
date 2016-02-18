<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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

    protected $sns_client = null;
    protected $message = "";
    protected $user=null;

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
        $this->sns_client = \App::make('Aws\Sns\SnsClient');

        $type = $this->argument('type');
        $topic_name = $this->option('topic_name');
        $this->message = $this->option('message');

        if(!$this->message) {
            $this->error('Message is required!');
            return;
        }

        if($type == "topic" && !$topic_name) {
            $this->error('Topic name is required to create a topic!');
            return;
        }

        $default_users = \DB::table('users')
                ->select(['id','push_token', 'target_arn_gcm'])
            ->where('email', 'dan@formula23.com')
            ->orWhere('email', 'sinisterindustries@yahoo.com')
            ->orWhere('email', 'chas2@f23.com')
            ->get();

//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
//            ->whereNotIn('id', function($q) {
//                $q->select('user_id')
//                    ->from('orders')
//                    ->where('status', 'done')
//                    ->where('confirm_at', '>', '2015-11-26')
//                    ->orWhere(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-12-03');
//            })->get();

        $zip_codes = explode(",", $this->option('zip_codes'));

        $users_qry = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(['users.id','push_token'])
            ->where('app_version', '1.4')
            ->where('push_token', '!=', '')
            ->where('status','done')
            ->where(function($q) use ($zip_codes) {
                if( ! empty($zip_codes)) {
                    foreach($zip_codes as $zip_code) {
                        $q->orWhere('location', 'like', '%'.$zip_code.'%');
                    }
                }
            })
            ->where('location', 'not like', '%31050 Venice Blvd%')
            ->whereNotIn('user_id', function($q) {
                $q->select('user_id')
                    ->from('orders')
                    ->whereIn('status', ['enroute', 'start', 'done'])
                    ->where('confirm_at', '>', \DB::raw('DATE_SUB(NOW(), INTERVAL 3 WEEK)'));
            })
            ->groupBy('user_id');

//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')->get();

        //daily anonymous users push
//        $users_qry = \DB::table('users')->select(['id','push_token'])->where('push_token', '!=', '')
//            ->where('email', 'like', '%squeegyapp-tmp.com%')
//            ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2016-02-10')
////            ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '<=', '2016-01-26')
//            ->orderBy('id');


        ///limits
//        if($this->option('take')) {
//            if($this->option('skip')) $users_qry->skip($this->option('skip'));
//            $users_qry->take($this->option('take'));
//        }

//        $users = $users_qry->get();

//        $users = \DB::select('SELECT users.id, users.push_token, users.`target_arn_gcm`
//          FROM orders, users
//          WHERE ((push_token IS NOT NULL AND push_token != \'\') OR target_arn_gcm IS NOT NULL)
//          AND orders.user_id = users.id
//          AND (charged > 0 OR discount_id IN (27,28,55,56,57,58))
//          AND status IN (\'assign\',\'enroute\',\'start\',\'done\')
//          AND orders.user_id NOT IN (SELECT user_id FROM orders WHERE status IN (\'enroute\', \'start\', \'done\')
//          AND confirm_at > DATE_SUB(NOW(), INTERVAL 1 WEEK))
//          GROUP BY user_id
//          LIMIT 200');


        //users & non-paid cust
//        $users = \DB::select('SELECT users.id, users.`push_token`, users.`target_arn_gcm`
//          FROM users
//          WHERE ((push_token IS NOT NULL AND `push_token` != \'\') OR `target_arn_gcm` IS NOT NULL)
//          AND users.id NOT IN (
//            SELECT users.id
//            FROM orders, users
//            WHERE orders.user_id = users.id
//            AND (charged > 0 OR discount_id IN (27,28,55,56,57,58))
//            AND `status` IN (\'assign\',\'enroute\',\'start\',\'done\') GROUP BY user_id)
//            LIMIT 2000
//            OFFSET 2000');


        $users = \DB::select('SELECT id, push_token, `target_arn_gcm` FROM users');

        $send_list = array_merge($users, $default_users);

        $this->info("user count: ".count($send_list));
        $this->info("publish message: ".$this->message);

        $this->sns_client = \App::make('Aws\Sns\SnsClient');

        if($type == "topic") {

//                $this->info('Topic created: '.$topic_name);
//
//                if($this->argument('env') == "test") {
//
//                } else {
//
//                    $resp = $this->sns_client->CreateTopic(['Name' => $topic_name]);
//
//                    $topic_arn = $resp->get('TopicArn');
//                    $this->info("TopicArn: ".$topic_arn);
//
//                    foreach($send_list as $user) {
//                        if (empty($user->push_token) && empty($user->target_arn_gcm)) continue;
//
//                        $this->sns_client->Subscribe([
//                            'TopicArn' => $topic_arn,
//                            'Protocol' => 'application',
//                            'Endpoint' => $this->push_token($user),
//                        ]);
//                        $this->_output($user);
//                    }
//
//                    $this->info('Publish to TopicArn');
//                    $this->publish($topic_arn);
//
//                }

        } else {
            foreach ($send_list as $this->user) {
                $this->publish();
            }
        }

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
			['env', InputArgument::OPTIONAL, 'Run as test or live', 'test'],
			['type', InputArgument::OPTIONAL, 'Create a topic or send direct message', 'direct'],
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
			['message', null, InputOption::VALUE_REQUIRED, 'The message to send.', null],
			['topic_name', null, InputOption::VALUE_OPTIONAL, 'Topic name.', null],
			['zip_codes', null, InputOption::VALUE_OPTIONAL, 'Zip codes', null],
            ['skip', null, InputOption::VALUE_OPTIONAL, 'Skip', null],
            ['take', null, InputOption::VALUE_OPTIONAL, 'Take', null],
		];
	}

    /**
     * @param $id
     * @param $push_token
     * @internal param $user
     */
    protected function _output($id, $push_token)
    {
        $this->info('user id: ' . $id." -- ".$push_token);
    }

    private function publish()
    {
        foreach(['push_token', 'target_arn_gcm'] as $endpoint_field) {

            if(empty($this->user->{$endpoint_field})) continue;

            $endpoint_arn = $this->user->{$endpoint_field};

            $target = (preg_match('/gcm/i', $endpoint_arn) ? "gcm" : "apns" );

            try {

                if($this->argument('env') == "live") {

                    if($target=="apns") {
                        $platform = env('APNS');
                        $payload = [
                            'aps' => [
                                'alert' => $this->message,
                                'sound' => 'default',
                                'badge' => 0
                            ],
                        ];

                    } else {
                        $platform = env('GCM');
                        $payload = [
                            'data' => [
                                'title' => 'Squeegy',
                                'message' => $this->message,
                                'url' => "squeegy://"
                            ],
                        ];
                    }

                    $publish_payload = [
                        'TargetArn' => $endpoint_arn,
                        'MessageStructure' => 'json',
                        'Message' => json_encode([
                            'default' => $this->message,
                            $platform => json_encode($payload)
                        ]),
                    ];

                    $this->sns_client->publish($publish_payload);
                }

                $this->_output($this->user->id, $endpoint_arn);

            } catch(\Exception $e) {
                $this->error($e->getMessage().'- '.$this->user->id.": ".$endpoint_arn);
            }
        }

        return;
    }

}
