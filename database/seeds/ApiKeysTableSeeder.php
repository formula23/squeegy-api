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
         * Squeegy consumer app
         */
        $apiKey->key = "bf9c37004d3155881b48fe54ec68024809a8e723";
        $apiKey->user_id = 0;
        $apiKey->level = 10;
        $apiKey->ignore_limits = 1;
        $apiKey->save();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
