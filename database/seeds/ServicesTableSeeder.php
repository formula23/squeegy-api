<?php

use App\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Service::truncate();

        Service::create([
            'name' => 'Exterior',
            'price' => '2400',
            'details' => '["Exterior Wash", "Wheels & Tire Dressing", "Exterior Windows"]',
            'time' => '25',
        ]);

        Service::create([
            'name' => 'Full',
            'price' => '3400',
            'details' => '["Exterior Wash", "Wheels & Tire Dressing", "Int/Ext Windows", "Floor & Seat Vacuum", "Dash Wipe Down"]',
            'time' => '45',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}