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
            'name'=>'Coral Circle - El Segundo',
            'location_name'=>'Coral Circle - El Segundo',
            'location'=>[
                "city"=>"El Segundo",
                "lon"=>-118.384138,
                "lat"=>33.911827,
                "street"=>"354 Coral Circle",
                "zip"=>"90245",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.916327,"lng":-118.385238},{"lat":33.916346,"lng":-118.383209},{"lat":33.910219,"lng":-118.383163},{"lat":33.909292,"lng":-118.385356}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Tuesday',
            'day_of_week'=>2,
            'next_date'=>Carbon::createFromDate(2016, 07, 12),
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
