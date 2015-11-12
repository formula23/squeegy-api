<?php

use App\User;
use App\WasherDefaultLocation;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;
use Symfony\Component\Debug\Exception\FatalErrorException;

class WorkerDefaultLocationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        WasherDefaultLocation::truncate();

        $default_location_map = [
            'dan@squeegy.com' => ['latitude' => 33.984285, 'longitude' => -118.406087],
            'andrew@squeegy.com' => ['latitude' => 33.956813, 'longitude' => -118.441428],
//            'jake@squeegy.com' => ['latitude' => 33.825557, 'longitude' => -118.388577], // zone 2 - southbay
            'jake@squeegy.com' => ['latitude' => 34.010352, 'longitude' => -118.457714], //zone 1 - LA
            'ricardo@squeegy.com' => ['latitude' => 34.041921, 'longitude' => -118.424827],
            'daniel@squeegy.com' => ['latitude' => 34.098085, 'longitude' => -118.326694],
        ];

        foreach($default_location_map as $worker_email=>$default_coords) {
            try {
                $user = User::where('email', $worker_email)->first();

                if(!$user) {
                    print $worker_email.": Not found! - Skipping\n";
                    continue;
                }

                $user->default_location()
                    ->create($default_coords);

                print $worker_email . "  updated!\n";

            } catch (\Exception $e) {}

        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
