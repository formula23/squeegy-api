<?php

use App\Service;
use Illuminate\Database\Seeder;
use App\Partner;
// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class PartnerTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        DB::statement('truncate table partner_days');
        DB::statement('truncate table partner_service');
        
        Partner::truncate();

        $partner = Partner::create([
            'name'=>'BuzzFeed',
            'location'=>'{"city":"Los Angeles","lon":-118.321797,"lat":34.098230,"street":"6087 Sunset Blvd","zip":"90028","state":"CA"}',
            'geo_fence'=>'[{"lat":34.098939,"lng":-118.322376},{"lat":34.098912,"lng":-118.320562},{"lat":34.098015,"lng":-118.320562},{"lat":34.098024,"lng":-118.322333}]',
        ]);

        $partner->days()->create([
            'day'=>'Thursday',
            'day_of_week'=>4,
            'time_start'=>'9:00am',
            'time_end'=>'6:00pm',
        ]);

        foreach( [1=>2000, 2=>2500] as $service_id=>$price_override) {
            $service = Service::find($service_id);

            $partner->services()->save($service, ['price'=>$price_override]);

        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

    }
}
