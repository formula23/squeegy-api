<?php

use App\Location;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class LocationsTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        foreach(range(1, 10) as $index)
        {
            //get random user to assign to car
            $user_ids = User::lists('id');

            Location::create([
                'user_id' => $user_ids[array_rand($user_ids)],
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