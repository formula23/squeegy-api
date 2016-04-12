<?php

namespace App\Console\Commands;

use App\User;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class ImportMixpanel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import-mixpanel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all the users to mixpanel and attribute their orders.';

    protected $mixpanel;

    protected $mixpanel_payload=[];

    protected $endpoint = "http://api.mixpanel.com/engage/";

    protected $batch = [];

    protected $token = "";

    protected $user;
    
    /**
     * Create a new command instance.
     *
     * @param LaravelMixpanel $mixpanel
     */
    public function __construct(LaravelMixpanel $mixpanel)
    {
        parent::__construct();

        $this->token = config('services.mixpanel.token');
        $this->curl_client = new Client();
        $this->mixpanel = $mixpanel;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batched_users=[];

        User::customers()->chunk(1000, function($users) {

            foreach($users as $this->user) {

                //delete
//                $batched_users[] = $this->get_properties('$delete', "");
//
//                if(count($batched_users)==50) {
//                    $this->send_batch($batched_users);
//                    $batched_users=[];
//                }
//
//                continue;

                
                $batched_users[] = $this->get_properties('$set', $this->profile_props());

                if(count($batched_users)==50) {
                    $this->send_batch($batched_users);
                    $batched_users=[];
                }

                foreach($this->user->completedPaidOrders()->get() as $order) {

                    $batched_users[] = $this->get_properties('$append', $this->transaction_props($order));

                    if(count($batched_users)==50) {
                        $this->send_batch($batched_users);
                        $batched_users=[];
                    }

                }

            }

        });

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

    
    protected function profile_props()
    {
        $properties=[];
        
        $properties['$email'] = $this->user->email;
        $properties['Is Anonymous'] = ( $this->user->is_anon() ? true : false );
        $properties['$created'] = ($this->user->created_at
            ? $this->user->created_at->toAtomString()
            : null);
        $properties['Segment ID'] = ( $this->user->segment ? $this->user->segment->segment_id : 0 );

        if( ! $this->user->is_anon()) {
            $properties['$first_name'] = $this->user->first_name();
            $properties['$last_name'] = $this->user->last_name();
            $properties['$name'] = $this->user->name;
            $properties['$phone'] = substr($this->user->phone, 2);
            $properties['Gender'] = $this->user->gender;
            $properties['Age Range'] = $this->user->age_range;

            $properties["Available Credits"] = $this->user->availableCredit()/100;
            $properties["Referral Code"] = $this->user->referral_code;

            $properties["Lash Wash At"] = ( ! empty($this->user->segment->last_wash_at) ? $this->user->segment->last_wash_at->toAtomString() : "");
            $properties["Lash Wash Type"] = ( ! empty($this->user->lastWash()) ? $this->user->lastWash()->service->name : "" );
        }
        
        return $properties;
    }

    protected function transaction_props($order)
    {
        $charged = $order->charged;

        if(in_array($order->discount_id, array_keys($promo_prices = Config::get('squeegy.groupon_gilt_promotions')))) {
            $charged = $promo_prices[$order->discount_id];
        }

        return [
            '$transactions' => [
                '$time' => $order->done_at->toAtomString(),
                '$amount' => ($charged/100),
            ]
        ];
    }

    protected function get_properties($operation='$set', $values="")
    {
        $payload = $this->user_payload();
        $payload[$operation] = $values;
        return $payload;
    }
    
    
    protected function user_payload()
    {
        return [
            '$token' => $this->token,
            '$distinct_id' => $this->user->id,
            '$ignore_time'=> true,
            '$ignore_alias'=> true,
            '$ip'=> 0,
        ];
    }
    
}
