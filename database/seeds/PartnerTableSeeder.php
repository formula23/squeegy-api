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
            'name'=>'ReGreen',
            'location_name'=>'ReGreen',
            'location'=>[
                "city"=>"El Segundo",
                "lat"=>33.917166,
                "lon"=>-118.414451,
                "street"=>"120 Standard St.",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.917883,"lng":-118.414709},{"lat":33.917889,"lng":-118.414121},{"lat":33.916393,"lng":-118.414135},{"lat":33.916405,"lng":-118.414703}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Friday',
            'day_of_week'=>5,
            'next_date'=>Carbon::createFromDate(2016, 07, 15),
            'time_start'=>'9:00am',
            'time_end'=>'3:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }
        
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
