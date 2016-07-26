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
            'name'=>'Box Brothers',
            'location_name'=>'Box Brothers',
            'location'=>[
                "city"=>"Inglewood",
                "lat" => 33.966290,
                "lon" => -118.355872,
                "street"=>"220 W Ivy Ave",
                "zip"=>"90302",
                "state"=>"CA"
            ],
            'geo_fence'=>'[{"lat":33.965688, "lng":-118.357221},{"lat":33.967043, "lng":-118.355576},{"lat":33.966437, "lng":-118.354373},{"lat":33.966529, "lng":-118.353649},{"lat":33.965597, "lng":-118.353441},{"lat":33.965243, "lng":-118.355983}]',
            'is_active'=>1,
        ]);

        $partner->days()->create([
            'day'=>'Friday',
            'day_of_week'=>5,
            'next_date'=>Carbon::createFromDate(2016, 07, 29),
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
