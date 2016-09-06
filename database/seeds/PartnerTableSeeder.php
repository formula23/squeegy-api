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
            'name'=>"550 Continental",
            'location_name'=>"550 Continental",
            'location'=>[
                "city"=>"El Segundo",
                "lat" => 33.922120,
                "lon" => -118.390253,
                "street"=>"550 Continental Blvd",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.922507, "lng":-118.391664},{"lat":33.922554, "lng":-118.389045},{"lat":33.921698, "lng":-118.389018},{"lat":33.921639, "lng":-118.390938}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Wednesday',
            'day_of_week'=>3,
            'next_date'=>Carbon::createFromDate(2016, 9, 7),
            'time_start'=>'8:00am',
            'time_end'=>'5:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }

        //////

//        $partner = Partner::create([
//            'name'=>"King's Arch - Sunset",
//            'location_name'=>"King's Arch - Sunset",
//            'location'=>[
//                "city"=>"Hollywood",
//                "lat" => 34.098264,
//                "lon" => -118.331619,
//                "street"=>"6515 W Sunset Blvd",
//                "zip"=>"90028",
//                "state"=>"CA"
//            ],
//            'geo_fence'=>'[{"lat":34.098740, "lng":-118.331734},{"lat":34.098744, "lng":-118.331499},{"lat":34.098122, "lng":-118.331495},{"lat":34.098116, "lng":-118.331730}]',
//            'is_active'=>1,
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Friday',
//            'day_of_week'=>5,
//            'next_date'=>Carbon::createFromDate(2016, 8, 19),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'bi-weekly',
//        ]);
//
//        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
//            $service = Service::find($service_id);
//            $partner->services()->save($service, ['price'=>$price_override]);
//        }

//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
