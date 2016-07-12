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
            'name'=>'400 Continental Building',
            'location_name'=>'400 Continental Building',
            'location'=>[
                "city"=>"El Segundo",
                "lat"=>33.920542,
                "lon"=>-118.389769,
                "street"=>"400 Continental Blvd",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.921701,"lng":-118.390946},{"lat":33.921701,"lng":-118.389074},{"lat":33.919814,"lng":-118.389028},{"lat":33.919627,"lng":-118.390759}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Wednesday',
            'day_of_week'=>3,
            'next_date'=>Carbon::createFromDate(2016, 07, 13),
            'time_start'=>'9:00am',
            'time_end'=>'6:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }
        
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
