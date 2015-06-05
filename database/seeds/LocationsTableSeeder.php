<?php

use App\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class LocationsTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        foreach(range(1, 10) as $index)
        {

            Location::create([
                'user_id' => 1,
                'address1' => $faker->buildingNumber.' '.$faker->streetName,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zip' => $faker->postcode,
                'lat' => $faker->latitude,
                'lng' => $faker->longitude,
            ]);
        }
    }

}