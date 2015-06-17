<?php

use App\ServiceCoords;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ServiceCoordsTableSeeder extends Seeder
{
    public function run()
    {
        $coords = [
            "33.930443" => "-118.437694",
            "33.930158" => " -118.368171",
            "33.897250" => " -118.369372",
            "33.873737" => " -118.342593",
            "33.858200" => " -118.327659",
            "33.858342" => " -118.378470",
            "33.852070" => " -118.400443",
        ];

        foreach($coords as $lat=>$lng) {
            ServiceCoords::create([
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }

    }
}
