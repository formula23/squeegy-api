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
            'name'=>'Mirada/Logan TV',
            'location_name'=>'Mirada/Logan TV',
            'location'=>[
                "city"=>"Los Angeles",
                "lat" => 33.988823,
                "lon" => -118.438634,
                "street"=>"4235 Redwood Ave.",
                "zip"=>"90066",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.989188,"lng":-118.438730},{"lat":33.988729,"lng":-118.438358},{"lat":33.988230,"lng":-118.439404},{"lat":33.988407,"lng":-118.439590},{"lat":33.987866, "lng":-118.440765},{"lat":33.988076, "lng":-118.440948}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Thursday',
            'day_of_week'=>4,
            'next_date'=>Carbon::createFromDate(2016, 07, 21),
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
