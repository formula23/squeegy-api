<?php

use App\Washer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class WashersTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        foreach(range(1, 5) as $index)
        {

            Washer::create([
                'name' => $faker->name(),
                'phone' => substr($faker->phoneNumber, 0, 15),
                'photo' => $faker->imageUrl(70, 70, 'people'),
            ]);
        }
    }

}