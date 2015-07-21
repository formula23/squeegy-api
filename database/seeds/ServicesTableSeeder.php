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
            'price' => '2400',
            'details' => '["- Exterior Wash and Dry", "- 100% Hand Wash", "- Light Wheel Wipe Down", "- Clean Exterior Windows", "- Clean & Dress Tires"]',
            'time' => '25',
        ]);

        Service::create([
            'name' => 'Classic',
            'price' => '3400',
            'details' => '["- Express Wash +", "- Interior Floor Vacuum", "- Interior Seat Vacuum", "- Clean Windows In & Out", "- Dash Wipe Down"]',
            'time' => '45',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}