<?php

use Illuminate\Database\Seeder;
use Chrisbjr\ApiGuard\Models\ApiKey;
use Chrisbjr\ApiGuard\Models\ApiLog;

class ApiKeysTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        $apiKey = new ApiKey();
        $apiKey->truncate();

        $apiLog = new ApiLog();
        $apiLog->truncate();

        /**
         * Local/Dev/Stage
         */
        $apiKey->key = "bf9c37004d3155881b48fe54ec68024809a8e723";
        $apiKey->user_id = 0;
        $apiKey->level = 10;
        $apiKey->ignore_limits = 1;
        $apiKey->save();

        /**
         * Production
         */
        $apiKey->key = "7b1731fc7d961cd201ff9d682ba876d59f46c5b4";
        $apiKey->user_id = 0;
        $apiKey->level = 10;
        $apiKey->ignore_limits = 1;
        $apiKey->save();


        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
