<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use CampaignMonitor;
use Config;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GuzzleHttp\Client;


class FixLastWash extends Command
{
    protected $endpoint = "http://api.mixpanel.com/engage/";

    protected $batch = [];

    protected $token = "";
    protected $mixpanel;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-last-wash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the last wash for a given user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LaravelMixpanel $laravelMixpanel)
    {
        parent::__construct();

        $this->token = config('services.mixpanel.token');
        $this->curl_client = new Client();
        $this->mixpanel = $laravelMixpanel;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rows = \DB::select('SELECT * FROM user_segments, orders WHERE user_segments.user_id = orders.user_id AND last_wash_at IS NULL AND orders.done_at IS NOT NULL GROUP BY user_segments.user_id');

        $subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

        $all_subscriber_data=[];
        $mixpanel_batch=[];

        foreach($rows as $row) {

            $user = User::find($row->user_id);
            if($last_wash = $user->lastWash()) {

                $user->segment->last_wash_at = $last_wash->done_at;

                $user->segment->save();

                $subscriber_data = [
                    'EmailAddress' => $user->email,
                    'CustomFields' => [
                        ['Key'=>'LastWash', 'Value'=>$last_wash->done_at->format('Y/m/d')],
                    ]
                ];

                $all_subscriber_data[] = $subscriber_data;

                $mixpanel_batch[] = [
                    '$token' => $this->token,
                    '$distinct_id' => $user->id,
                    '$ignore_time'=> true,
                    '$ignore_alias'=> true,
                    '$ip'=> 0,
                    '$set'=> ["Lash Wash At" => $last_wash->done_at->toAtomString()]
                ];

                if(count($mixpanel_batch)==50) {
                    $this->send_batch($mixpanel_batch);
                    $mixpanel_batch=[];
                }

            }

        }

        if(count($mixpanel_batch)) {
            $this->send_batch($mixpanel_batch);
        }


        $update_resp = $subscriber->import($all_subscriber_data, false, false, false);

        if($update_resp->was_successful()) {
            $this->info('Update Success');
        } else {

            $this->info('Failed with code: '.$update_resp->http_status_code);
//				var_dump($import_resp->response);

            if($update_resp->response->ResultData->TotalExistingSubscribers > 0) {
                $this->info('Updated '.$update_resp->response->ResultData->TotalExistingSubscribers.' existing subscribers in the list');
            } else if($update_resp->response->ResultData->TotalNewSubscribers > 0) {
                $this->info('Added '.$update_resp->response->ResultData->TotalNewSubscribers.' to the list');
            } else if(count($update_resp->response->ResultData->DuplicateEmailsInSubmission) > 0) {
                $this->info(count($update_resp->response->ResultData->DuplicateEmailsInSubmission).' were duplicated in the provided array.');
            }

            $this->info('The following emails failed to import correctly.');
            var_dump($update_resp->response->ResultData->FailureDetails);
        }

    }

    protected function send_batch($batch)
    {

        $this->info("Send Batch: ".count($batch));

        $base64 = base64_encode(json_encode($batch));

        $res = $this->curl_client->request('POST', $this->endpoint, [
            'form_params' => [
                'data'=>$base64,
                'verbose'=>1,
                'api_key'=>config('services.mixpanel.api_key'),
            ]
        ]);

        $this->info($res->getStatusCode());
        $this->info($res->getHeaderLine('content-type'));
        $this->info($res->getBody());

    }
}
