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
            'name'=>'Skechers',
            'location_name'=>'Skechers',
            'location'=>[
                "city"=>"Manhattan Beach",
                "lat" => 33.878281,
                "lon" => -118.396552,
                "street"=>"225 S Sepulveda Blvd",
                "zip"=>"90266",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.878616, "lng":-118.396922},{"lat":33.878604, "lng":-118.396129},{"lat":33.877928, "lng":-118.396127},{"lat":33.877924, "lng":-118.396912}]',
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
