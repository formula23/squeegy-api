<?php

use App\Addon;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class AddonsTableSeeder extends Seeder
{
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Addon::truncate();

        Addon::create([
            'name'=>'Carbauba Hand Wax',
            'description'=>'',
            'price'=>'2500',
        ]);

        Addon::create([
            'name'=>'Leather Conditioning',
            'description'=>'',
            'price'=>'1500',
        ]);

        Addon::create([
            'name'=>'Exterior Plastic Dressing',
            'description'=>'',
            'price'=>'1500',
        ]);

        Addon::create([
            'name'=>'Tree Sap Removal',
            'description'=>'',
            'price'=>'2000',
        ]);

        Addon::create([
            'name'=>'Air Freshener',
            'description'=>'',
            'price'=>'400',
        ]);

        Addon::create([
            'name'=>'Pet Hair Removal',
            'description'=>'',
            'price'=>'2000',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
