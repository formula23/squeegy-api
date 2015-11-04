<?php

use App\User;
use App\WasherDefaultLocation;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class WorkerDefaultLocationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        WasherDefaultLocation::truncate();

        $default_location_map = [
            1 => ['latitude' => 33.984316, 'longitude' => -118.406227],
            2 => ['latitude' => 33.957200, 'longitude' => -118.441594],
        ];

        foreach([1,2] as $user_id) {
            User::find($user_id)
                ->default_location()
                ->create($default_location_map[$user_id]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
