<?php

use App\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        Service::truncate();

        Service::create([
            'name' => 'Exterior',
            'price' => '2400',
            'details' => 'This is the basic wash.',
            'time' => '25',
        ]);

        Service::create([
            'name' => 'Full',
            'price' => '3400',
            'details' => 'This is the extreme wash.. inside and out...',
            'time' => '45',
        ]);
    }

}