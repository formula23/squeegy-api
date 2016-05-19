<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class SanitizeDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sanitize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all user records with my phone, push and stripe id';

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
        if(env('APP_ENV')=='production') {
            $this->info('Not available in production');
            return;
        }

        try {
            \DB::table('users')->update([
                'push_token'=>'arn:aws:sns:us-east-1:171738602425:endpoint/APNS_SANDBOX/Squeegy/5212b7f5-0d68-34e4-943a-ea4014afb885',
                'target_arn_gcm'=>'arn:aws:sns:us-east-1:171738602425:endpoint/GCM/Squeegy-Android/4c03a096-65e0-33a8-9bb3-95ac49f2d2cf',
                'phone'=>'+13106004938',
                'stripe_customer_id'=>'cus_7fTOZi8a6nwkNE',
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
