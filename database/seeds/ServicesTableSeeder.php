<?php

use App\Service;
use App\ServiceAttrib;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Service::truncate();
        ServiceAttrib::truncate();
        ////// ***** EXPRESS ***** ///////

        $express = Service::create([
            'name' => 'Express',
            'price' => '2500',
            'details' => '["- Exterior Wash & Wax", "- Clean Exterior Windows", "- Light Wheel Wipe Down", "- Clean & Dress Tires"]',
            'time' => '30',
            'time_label' => '30 - 45 Minutes (Depending on Vehicle)',
            'sequence' => 2,
            'is_active' => 1,
        ]);

        $express->attribs()->create([
            'vehicle_type' => 'Car',
            'vehicle_size' => 'Midsize',
            'etc' => 30,
            'surcharge' => 0,
        ]);

        $express->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Midsize',
            'etc' => 35,
            'surcharge' => 0,
        ]);

        $express->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Large',
            'etc' => 40,
            'surcharge' => 400,
        ]);

        ////// ***** CLASSIC ***** ///////

        $classic = Service::create([
            'name' => 'Classic',
            'price' => '3900',
            'details' => '["- Express Wash +", "- Floor & Seat Vacuum", "- Dash & Panel Wipe Down", "- Clean Interior Windows"]',
            'time' => '60',
            'time_label' => '45 - 75 Minutes (Depending on Vehicle)',
            'sequence' => 3,
            'is_active' => 1,
        ]);

        $classic->attribs()->create([
            'vehicle_type' => 'Car',
            'vehicle_size' => 'Midsize',
            'etc' => 45,
            'surcharge' => 0,
        ]);

        $classic->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Midsize',
            'etc' => 60,
            'surcharge' => 0,
        ]);

        $classic->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Large',
            'etc' => 75,
            'surcharge' => 600,
        ]);

        ////// ***** SQUEEGY ***** ///////

        $squeegy = Service::create([
            'name' => 'Squeegy',
            'price' => '1500',
            'details' => '["- Exterior Wash"]',
            'time' => '20',
            'time_label' => '20 - 30 Minutes (Depending on Vehicle)',
            'sequence' => 1,
            'is_active' => 1,
        ]);

        $squeegy->attribs()->create([
            'vehicle_type' => 'Car',
            'vehicle_size' => 'Midsize',
            'etc' => 20,
            'surcharge' => 0,
        ]);

        $squeegy->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Midsize',
            'etc' => 30,
            'surcharge' => 0,
        ]);

        $squeegy->attribs()->create([
            'vehicle_type' => 'Non-Car',
            'vehicle_size' => 'Large',
            'etc' => 40,
            'surcharge' => 200,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}