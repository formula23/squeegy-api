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
            'name'=>'K-Bell',
            'location_name'=>'K-Bell',
            'location'=>[
                "city"=>"Inglewood",
                "lon"=>-118.361820,
                "lat"=>33.966870,
                "street"=>"550 N Oak St.",
                "zip"=>"90302",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.968294,"lng":-118.365714},{"lat":33.968534,"lng": -118.362742},{"lat":33.966701,"lng":-118.359427},{"lat":33.965517,"lng":-118.359642},{"lat":33.965838,"lng":-118.364255},{"lat":33.965179,"lng":-118.366261},{"lat":33.967181,"lng":-118.366605},{"lat":33.967422,"lng":-118.365575}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Friday',
            'day_of_week'=>5,
            'next_date'=>Carbon::createFromDate(2016, 07, 15),
            'time_start'=>'8:00am',
            'time_end'=>'4:00pm',
            'frequency'=>'weekly',
        ]);

        foreach( [1=>1800, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);
            $partner->services()->save($service, ['price'=>$price_override]);
        }
        
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
