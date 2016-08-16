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
            'name'=>"Manhattan Towers",
            'location_name'=>"Manhattan Towers",
            'location'=>[
                "city"=>"Manhattan Beach",
                "lat" => 33.900856,
                "lon" => -118.392345,
                "street"=>"1240 Rosecrans Ave",
                "zip"=>"90266",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.901816, "lng":-118.392798},{"lat":33.901816, "lng":-118.390722},{"lat":33.900776, "lng":-118.390773},{"lat":33.900458, "lng":-118.392954}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Tuesday',
            'day_of_week'=>2,
            'next_date'=>Carbon::createFromDate(2016, 8, 16),
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
