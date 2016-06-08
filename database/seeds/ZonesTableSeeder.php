<?php

use App\Zone;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ZonesTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Zone::truncate();

        Zone::create([
            'name' => 'LA',
            'code' => '001',
        ]);

        Zone::create([
            'name' => 'South Bay',
            'code' => '002',
        ]);

        Zone::create([
            'name' => 'Encino',
            'code' => '003',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
