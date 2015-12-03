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

        $default_users = \DB::table('users')->select(['id','push_token'])->where('email', 'dan@formula23.com')->orWhere('email', 'sinisterindustries@yahoo.com')->get();

        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
            ->whereNotIn('id', function($q) {
                $q->select('user_id')
                    ->from('orders')
                    ->where('status', 'done')
                    ->where('confirm_at', '>', '2015-11-26')
                    ->orWhere(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-12-03');
            })->get();

        //daily anonymous users push
//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
//            ->where('email', 'like', '%squeegyapp-tmp.com%')
//            ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-12-01')
//            ->orderBy('id')->get();

        $send_list = array_merge($users, $default_users);

        $this->info("user count: ".count($send_list));
        $this->info("publish message: ".$this->message);

        if($this->argument('env') == "test") {

            foreach($send_list as $user) {
                $this->_output($user);
            }

        } else {

            $this->sns_client = \App::make('Aws\Sns\SnsClient');

            if($type == "topic") {

                $this->info('Topic created: '.$topic_name);

                $resp = $this->sns_client->CreateTopic(['Name' => $topic_name]);

                $topic_arn = $resp->get('TopicArn');
                $this->info("TopicArn: ".$topic_arn);

                foreach($send_list as $user) {
                    if(empty($user->push_token)) continue;

                    $this->sns_client->Subscribe([
                        'TopicArn' => $topic_arn,
                        'Protocol' => 'application',
                        'Endpoint' => $user->push_token,
                    ]);
                    $this->_output($user);
                }

                $this->info('Publish to TopicArn');
//                $this->publish($topic_arn);

            } else {

                foreach ($send_list as $user) {
                    if (empty($user->push_token)) continue;

                    $this->publish($user->push_token);
                    $this->_output($user);
                }
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
		];
	}

    /**
     * @param $user
     */
    protected function _output($user)
    {
        $this->info('user id: ' . $user->id." -- ".$user->push_token);
    }

    /**
     * @param $endpoint_arn
     */
    private function publish($endpoint_arn)
    {
        $aps_payload = [
            'aps' => [
                'alert' => $this->message,
                'sound' => 'default',
                'badge' => 1
            ],
        ];

        $message = json_encode([
            'default' => $this->message,
            env('APNS') => json_encode($aps_payload)
        ]);

        try {
            $this->sns_client->publish([
                'TargetArn' => $endpoint_arn,
                'MessageStructure' => 'json',
                'Message' => $message,
            ]);
        } catch(\Exception $e) {
            $this->error($e->getMessage().' : '.$endpoint_arn);
            \Bugsnag::notifyException($e);
        }

        return;
    }

}
