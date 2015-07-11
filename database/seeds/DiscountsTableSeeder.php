<?php

use App\Discount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class DiscountsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Discount::truncate();

        Discount::create([
            'name' => 'App Launch',
            'description' => 'Launch',
            'discount_type' => 'pct',
            'amount' => '20',
            'code' => 'LAUNCH20',
            'scope' => 'user',
            'frequency_rate' => 1,
            'new_customer' => 1,
            'is_active' => 1,
        ]);

        Discount::create([
            'name' => 'Beta Test',
            'description' => 'Beta',
            'discount_type' => 'pct',
            'amount' => '100',
            'code' => 'BETATEST',
            'scope' => 'system',
            'frequency_rate' => 0,
            'new_customer' => 0,
            'is_active' => 1,
        ]);

        Discount::create([
            'name' => 'Beta Test',
            'description' => 'Beta',
            'discount_type' => 'pct',
            'amount' => '50',
            'code' => 'BETA50',
            'scope' => 'system',
            'frequency_rate' => 0,
            'new_customer' => 0,
            'is_active' => 1,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints

    }
}
