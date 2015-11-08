<?php

use App\Zones;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ZonesTableSeeder extends Seeder
{
    public function run()
    {
        Zones::truncate();

        Zones::create([
            'name' => 'LA',
            'code' => '001',
        ]);

        Zones::create([
            'name' => 'South Bay',
            'code' => '002',
        ]);

    }
}
