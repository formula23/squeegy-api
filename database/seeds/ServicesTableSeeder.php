<?php

use App\Service;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {

        Service::create([
            'name' => 'Basic',
            'price' => '19',
            'details' => 'This is the basic wash',
            'time' => '20',
        ]);

        Service::create([
            'name' => 'Full Monty',
            'price' => '29',
            'details' => 'This is the fully monty wash.. inside and out.',
            'time' => '45',
        ]);
    }

}