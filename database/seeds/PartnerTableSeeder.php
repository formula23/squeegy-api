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
            'name'=>'Skechers Lot 1/2',
            'location_name'=>'Skechers Lot 1/2',
            'location'=>[
                "city"=>"Hermosa Beach",
                "lat" => 33.876219,
                "lon" => -118.396307,
                "street"=>"30th St",
                "zip"=>"90254",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.876378, "lng":-118.396433},{"lat":33.876384, "lng":-118.395838},{"lat":33.877482, "lng":-118.395867},{"lat":33.877493, "lng":-118.395301},{"lat":33.876817, "lng":-118.395287},{"lat":33.876766, "lng":-118.395790},{"lat":33.876751, "lng":-118.395776},{"lat":33.876344, "lng":-118.395795},{"lat":33.876327, "lng":-118.396129},{"lat":33.876067, "lng":-118.396136},{"lat":33.875106, "lng":-118.396117},{"lat":33.875044, "lng":-118.396684},{"lat":33.876003, "lng":-118.396694},{"lat":33.876083, "lng":-118.396446}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Wednesday',
            'day_of_week'=>3,
            'next_date'=>Carbon::createFromDate(2016, 8, 3),
            'time_start'=>'8:00am',
            'time_end'=>'5:00pm',
            'frequency'=>'weekly',
        ]);

        $partner->days()->create([
            'day'=>'Friday',
            'day_of_week'=>5,
            'next_date'=>Carbon::createFromDate(2016, 8, 5),
            'time_start'=>'8:00am',
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
