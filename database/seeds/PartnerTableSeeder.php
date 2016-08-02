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


//
//        $partner = Partner::create([
//            'name'=>'Mattel Tower',
//            'location_name'=>'Mattel Tower',
//            'location'=>[
//                "city"=>"El Segundo",
//                "lat" => 33.920633,
//                "lon" => -118.391523,
//                "street"=>"333 Continental Blvd",
//                "zip"=>"90245",
//                "state"=>"CA"
//            ],
//            'geo_fence'=>'[{"lat":33.921701, "lng":-118.392639},{"lat":33.921680, "lng":-118.391191},{"lat":33.920314, "lng":-118.390969},{"lat":33.920337, "lng":-118.392655}]',
//            'is_active'=>1,
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Monday',
//            'day_of_week'=>1,
//            'next_date'=>Carbon::createFromDate(2016, 8, 8),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Thursday',
//            'day_of_week'=>5,
//            'next_date'=>Carbon::createFromDate(2016, 8, 11),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        foreach( [1=>1500, 2=>2500] as $service_id=>$price_override) {
//            $service = Service::find($service_id);
//            $partner->services()->save($service, ['price'=>$price_override]);
//        }
//
//
//        //////
//
//        $partner = Partner::create([
//            'name'=>'Mattel MCLC',
//            'location_name'=>'Mattel MCLC',
//            'location'=>[
//                "city"=>"El Segundo",
//                "lat" => 33.920633,
//                "lon" => -118.391523,
//                "street"=>"1955 East Grand Avenue",
//                "zip"=>"90245",
//                "state"=>"CA"
//            ],
//            'geo_fence'=>'[{"lat":33.921653, "lng":-118.393637},{"lat":33.921666, "lng":-118.392758},{"lat":33.919722, "lng":-118.392752},{"lat":33.919726, "lng":-118.393622}]',
//            'is_active'=>1,
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Monday',
//            'day_of_week'=>1,
//            'next_date'=>Carbon::createFromDate(2016, 8, 8),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Thursday',
//            'day_of_week'=>5,
//            'next_date'=>Carbon::createFromDate(2016, 8, 11),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        foreach( [1=>1500, 2=>2500] as $service_id=>$price_override) {
//            $service = Service::find($service_id);
//            $partner->services()->save($service, ['price'=>$price_override]);
//        }
//
//        //////
//
//        $partner = Partner::create([
//            'name'=>'Mattel HTC',
//            'location_name'=>'Mattel HTC',
//            'location'=>[
//                "city"=>"El Segundo",
//                "lat" => 33.924213,
//                "lon" => -118.391295,
//                "street"=>"2031 East Mariposa Avenue",
//                "zip"=>"90245",
//                "state"=>"CA"
//            ],
//            'geo_fence'=>'[{"lat":33.926829, "lng":-118.391862},{"lat":33.926816, "lng":-118.390056},{"lat":33.924958, "lng":-118.390067},{"lat":33.924909, "lng":-118.389126},{"lat":33.923761, "lng":-118.389090},{"lat":33.923778, "lng":-118.391847}]',
//            'is_active'=>1,
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Monday',
//            'day_of_week'=>1,
//            'next_date'=>Carbon::createFromDate(2016, 8, 8),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Thursday',
//            'day_of_week'=>5,
//            'next_date'=>Carbon::createFromDate(2016, 8, 11),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        foreach( [1=>1500, 2=>2500] as $service_id=>$price_override) {
//            $service = Service::find($service_id);
//            $partner->services()->save($service, ['price'=>$price_override]);
//        }
//
//        //////
//
//        $partner = Partner::create([
//            'name'=>'Mattel Maple',
//            'location_name'=>'Mattel Maple',
//            'location'=>[
//                "city"=>"El Segundo",
//                "lat" => 33.927259,
//                "lon" => -118.390280,
//                "street"=>"2031 East Maple Avenue",
//                "zip"=>"90245",
//                "state"=>"CA"
//            ],
//            'geo_fence'=>'[{"lat":33.927659, "lng":-118.390900},{"lat":33.927655, "lng":-118.389721},{"lat":33.927058, "lng":-118.389721},{"lat":33.927035, "lng":-118.391786},{"lat":33.927331, "lng":-118.391668},{"lat":33.927511, "lng":-118.391435},{"lat":33.927682, "lng":-118.391094}]',
//            'is_active'=>1,
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Monday',
//            'day_of_week'=>1,
//            'next_date'=>Carbon::createFromDate(2016, 8, 8),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        $partner->days()->create([
//            'day'=>'Thursday',
//            'day_of_week'=>5,
//            'next_date'=>Carbon::createFromDate(2016, 8, 11),
//            'time_start'=>'8:00am',
//            'time_end'=>'5:00pm',
//            'frequency'=>'weekly',
//        ]);
//
//        foreach( [1=>1500, 2=>2500] as $service_id=>$price_override) {
//            $service = Service::find($service_id);
//            $partner->services()->save($service, ['price'=>$price_override]);
//        }
//
//        //////

        $partner = Partner::create([
            'name'=>'Fuhu',
            'location_name'=>'Fuhu',
            'location'=>[
                "city"=>"El Segundo",
                "lat" => 33.928646,
                "lon" => -118.397687,
                "street"=>"1700 E Walnut Ave",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.929005, "lng":-118.398170},{"lat":33.929004, "lng":-118.397500},{"lat":33.928135, "lng":-118.397476},{"lat":33.928122, "lng":-118.398159}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Monday',
            'day_of_week'=>1,
            'next_date'=>Carbon::createFromDate(2016, 8, 8),
            'time_start'=>'8:00am',
            'time_end'=>'5:00pm',
            'frequency'=>'weekly',
        ]);

        $partner->days()->create([
            'day'=>'Thursday',
            'day_of_week'=>5,
            'next_date'=>Carbon::createFromDate(2016, 8, 11),
            'time_start'=>'8:00am',
            'time_end'=>'5:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1500, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }

//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
