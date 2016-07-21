<?php

use App\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Partner;
// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class PartnerTableSeeder extends Seeder
{
    public function run()
    {
//        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

//        DB::statement('truncate table partner_days');
//        DB::statement('truncate table partner_service');
//        Partner::truncate();

        $partner = Partner::create([
            'name'=>'Coppersmith',
            'location_name'=>'Coppersmith',
            'location'=>[
                "city"=>"El Segundo",
                "lat" => 33.908819,
                "lon" => -118.383856,
                "street"=>"525 S Douglas St.",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.909645, "lng":-118.383341},{"lat":33.908650, "lng":-118.383328},{"lat":33.907832, "lng":-118.383677},{"lat":33.908144, "lng":-118.384715},{"lat":33.908816, "lng":-118.384376}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Tuesday',
            'day_of_week'=>2,
            'next_date'=>Carbon::createFromDate(2016, 07, 26),
            'time_start'=>'9:00am',
            'time_end'=>'5:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }
        
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
