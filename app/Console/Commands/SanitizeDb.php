<?php

namespace App\Console\Commands;

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
        try {
            \DB::raw("UPDATE users SET `push_token` = 'arn:aws:sns:us-east-1:171738602425:endpoint/APNS_SANDBOX/Squeegy/5212b7f5-0d68-34e4-943a-ea4014afb885', `phone` = '+13106004938', `stripe_customer_id` = 'cus_7fTOZi8a6nwkNE'");
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }
}
