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
            'name'=>"Nantworks",
            'code'=>'NANT',
            'location_name'=>"Nantworks",
            'location'=>[
                "city"=>"Culver City",
                "lat" => 34.014442,
                "lon" => -118.387984,
                "street"=>"9920 Jefferson Blvd",
                "zip"=>"90230",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":34.015132, "lng":-118.388305},{"lat":34.014092, "lng":-118.386460},{"lat":34.012922, "lng":-118.385390},{"lat":34.011072, "lng":-118.386528},{"lat":34.013548, "lng":-118.387771},{"lat":34.014013, "lng":-118.388603}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'open'=>Carbon::create(2016,9,27,8,0,0),
            'close'=>Carbon::create(2016,9,27,17,0,0),
            'frequency'=>'weekly',
            'order_cap'=>'20',
        ]);
        $partner->days()->create([
            'open'=>Carbon::create(2016,9,30,8,0,0),
            'close'=>Carbon::create(2016,9,30,17,0,0),
            'frequency'=>'weekly',
            'order_cap'=>'20',
        ]);

        foreach( [1=>1500, 2=>2000] as $service_id=>$price_override) {
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
