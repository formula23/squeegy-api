<?php

use App\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Service::truncate();

        Service::create([
            'name' => 'Express',
            'price' => '2900',
            'details' => '["- Exterior Wash & Wax", "- Clean Exterior Windows", "- Light Wheel Wipe Down", "- Clean & Dress Tires"]',
            'time' => '30',
            'time_label' => '30 - 45 Minutes (Depending on Vehicle)',
            'sequence' => 2,
            'is_active' => 1,
        ]);

        Service::create([
            'name' => 'Classic',
            'price' => '3900',
            'details' => '["- Express Wash +", "- Floor & Seat Vacuum", "- Dash & Panel Wipe Down", "- Clean Interior Windows"]',
            'time' => '60',
            'time_label' => '45 - 75 Minutes (Depending on Vehicle)',
            'sequence' => 3,
            'is_active' => 1,
        ]);

        Service::create([
            'name' => 'Squeegy',
            'price' => '1500',
            'details' => '["- Exterior Wash"]',
            'time' => '20',
            'time_label' => '20 - 30 Minutes (Depending on Vehicle)',
            'sequence' => 1,
            'is_active' => 1,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}