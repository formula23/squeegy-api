<?php

use App\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class VehiclesTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();

        foreach(range(1, 10) as $index)
        {

            Vehicle::create([
                'user_id' => 1,
                'year' => $faker->numberBetween(date('Y')-8, date('Y')),
                'make' => $faker->randomElement(array ('Honda','Mercedes','BMW', 'Infiniti')),
                'color' => $faker->randomElement(array ('Black','Gray','Blue', 'White', 'Red')),
                'type' => $faker->randomElement(array ('Sedan', 'SUV')),
//                'license_plate' => strtoupper($faker->randomDigit.$faker->randomLetter.$faker->randomLetter.$faker->randomLetter.$faker->randomNumber(3)),
                'license_plate' => strtoupper(Str::quickRandom(7)),
            ]);
        }
    }

}