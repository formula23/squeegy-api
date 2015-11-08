<?php

use App\Zone;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ZonesTableSeeder extends Seeder
{
    public function run()
    {
        Zone::truncate();

        Zone::create([
            'name' => 'LA',
            'code' => '001',
        ]);

        Zone::create([
            'name' => 'South Bay',
            'code' => '002',
        ]);

    }
}
