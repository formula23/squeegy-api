<?php

namespace App\Console\Commands;

use CampaignMonitor;
use App\User;
use Illuminate\Console\Command;
use Config;

class AddMissingUserToCM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-missing-cm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missing email addresses to CM';

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
    public function handle()
    {

        $subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

        User::customers()->where('email', 'not like', '%squeegyapp-tmp.com')->take(10)->chunk(1000, function($users) use ($subscriber) {

            foreach($users as $user) {

                $all_subscriber_data=[];

                $last_wash = $user->lastWash();

                $subscriber_data = [
                    'EmailAddress' => $user->email,
                    'Name' => $user->name,
                    'CustomFields' => [
                        ['Key'=>'SegmentID', 'Value'=>$user->segment->segment_id],
                        ['Key'=>'Device', 'Value'=>$user->device()],
                        ['Key'=>'LastWash', 'Value'=>($last_wash ? $last_wash->done_at->format('Y/m/d') : '')],
                        ['Key'=>'AvailableCredit', 'Value'=>$user->availableCredit()/100],
                        ['Key'=>'ReferralCode', 'Value'=>$user->referral_code],
                    ]
                ];

                $all_subscriber_data[] = $subscriber_data;

            }

            $import_resp = $subscriber->import($all_subscriber_data, false, false, false);

            if($import_resp->was_successful()) {
                $this->info('Import Success');
            } else {

                $this->info('Failed with code: '.$import_resp->http_status_code);
//				var_dump($import_resp->response);

                if($import_resp->response->ResultData->TotalExistingSubscribers > 0) {
                    $this->info('Updated '.$import_resp->response->ResultData->TotalExistingSubscribers.' existing subscribers in the list');
                } else if($import_resp->response->ResultData->TotalNewSubscribers > 0) {
                    $this->info('Added '.$import_resp->response->ResultData->TotalNewSubscribers.' to the list');
                } else if(count($import_resp->response->ResultData->DuplicateEmailsInSubmission) > 0) {
                    $this->info(count($import_resp->response->ResultData->DuplicateEmailsInSubmission).' were duplicated in the provided array.');
                }

                $this->info('The following emails failed to import correctly.');
                var_dump($import_resp->response->ResultData->FailureDetails);
            }


        });



    }
}
