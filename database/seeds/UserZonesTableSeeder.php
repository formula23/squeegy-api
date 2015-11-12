<?php

use App\User;
use App\Zone;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class UserZonesTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $default_location_map = [
            'dan@squeegy.com' => 1,
            'andrew@squeegy.com' => 1,
            'jake@squeegy.com' => 1,
            'ricardo@squeegy.com' => 1,
            'daniel@squeegy.com' => 1,
        ];

        foreach($default_location_map as $worker_email=>$zone_id) {
            try {
                $user = User::where('email', $worker_email)->first();

                if(!$user) {
                    print $worker_email.": Not found! - Skipping\n";
                    continue;
                }
                $zone = Zone::find($zone_id);
                $user->zones()->save($zone);

                print $worker_email . " zone updated!\n";

            } catch (\Exception $e) {}

        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
