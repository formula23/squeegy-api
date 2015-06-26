<?php

use App\User;
use App\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class VehiclesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Vehicle::truncate();

        $faker = Faker::create();

        foreach(range(1, 50) as $index)
        {
            //get random user to assign to car
            $user_ids = User::lists('id');

            $vehicle_make = $faker->randomElement(['Honda','Mercedes','BMW', 'Infiniti']);
            $vehicle_models = ['Honda'=>'Accord', 'Mercedes'=>'C300', 'BMW'=>'535i', 'Infiniti'=>'G37S'];

            Vehicle::create([
                'user_id' => $user_ids[array_rand($user_ids)],
                'year' => $faker->numberBetween(date('Y')-8, date('Y')),
                'make' => $vehicle_make,
                'model' => $vehicle_models[$vehicle_make],
                'color' => $faker->randomElement(array ('Black','Gray','Blue', 'White', 'Red')),
                'type' => $faker->randomElement(array ('Sedan', 'SUV')),
                'license_plate' => strtoupper(Str::quickRandom(7)),
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}