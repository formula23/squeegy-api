<?php

use Illuminate\Database\Seeder;


class ApiKeysTableSeeder extends Seeder
{
    public function run()
    {
        $apiKey = \App::make('Chrisbjr\ApiGuard\Models\ApiKey');
        $apiKey->truncate();
        $apiLog = \App::make('Chrisbjr\ApiGuard\Models\ApiLog');
        $apiLog->truncate();


        $apiKey = \App::make(Config::get('apiguard.model', 'Chrisbjr\ApiGuard\Models\ApiKey'));
        $apiKey->key = "bf9c37004d3155881b48fe54ec68024809a8e723";
        $apiKey->user_id = 0;
        $apiKey->level = 10;
        $apiKey->ignore_limits = 1;
        $apiKey->save();
    }
}
