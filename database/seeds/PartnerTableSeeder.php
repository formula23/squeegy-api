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
            'name'=>'Marina 41 Apartments',
            'location_name'=>'Marina 41 Apartments',
            'location'=>[
                "city"=>"Marina del Rey",
                "lon"=>-118.459605,
                "lat"=>33.980095,
                "street"=>"4157 Via Marina",
                "zip"=>"90292",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.979067,"lng":-118.459148},{"lat":33.979054,"lng":-118.460106},{"lat":33.978425,"lng":-118.461120},{"lat":33.979490,"lng":-118.461920},{"lat":33.980786,"lng":-118.459599},{"lat":33.979980,"lng":-118.459218}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Sunday',
            'day_of_week'=>0,
            'next_date'=>Carbon::createFromDate(2016, 07, 10),
            'time_start'=>'10:00am',
            'time_end'=>'6:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1900, 2=>2900] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }
        
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
