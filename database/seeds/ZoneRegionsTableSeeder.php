<?php

use App\Region;
use App\Zone;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ZoneRegionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Region::truncate();

        $regions = [
            1 => [
                ['postal_code'=>'90272'],
                ['postal_code'=>'90049'],
                ['postal_code'=>'90025'],
                ['postal_code'=>'90027'],
                ['postal_code'=>'90029'],
                ['postal_code'=>'90024'],
                ['postal_code'=>'90067'],
                ['postal_code'=>'90025'],
                ['postal_code'=>'90095'],
                ['postal_code'=>'90210'],
                ['postal_code'=>'90211'],
                ['postal_code'=>'90212'],
                ['postal_code'=>'90069'],
                ['postal_code'=>'90048'],
                ['postal_code'=>'90046'],
                ['postal_code'=>'90028'],
                ['postal_code'=>'90038'],
                ['postal_code'=>'90004'],
                ['postal_code'=>'90008'],
                ['postal_code'=>'90077'],
                ['postal_code'=>'90020'],
                ['postal_code'=>'90010'],
                ['postal_code'=>'90005'],
                ['postal_code'=>'90006'],
                ['postal_code'=>'90019'],
                ['postal_code'=>'90035'],
                ['postal_code'=>'90036'],
                ['postal_code'=>'90016'],
                ['postal_code'=>'90018'],
                ['postal_code'=>'90034'],
                ['postal_code'=>'90062'],
                ['postal_code'=>'90064'],
                ['postal_code'=>'90047'],
                ['postal_code'=>'90043'],
                ['postal_code'=>'90232'],
                ['postal_code'=>'90230'],
                ['postal_code'=>'90066'],
                ['postal_code'=>'90094'],
                ['postal_code'=>'90045'],
                ['postal_code'=>'90056'],
                ['postal_code'=>'90293'],
                ['postal_code'=>'90292'],
                ['postal_code'=>'90291'],
                ['postal_code'=>'90301'],
                ['postal_code'=>'90302'],
                ['postal_code'=>'90401'],
                ['postal_code'=>'90402'],
                ['postal_code'=>'90403'],
                ['postal_code'=>'90404'],
                ['postal_code'=>'90405'],
                ['postal_code'=>'90073'],
            ],
            2 => [
                ['postal_code'=>'90245'],
                ['postal_code'=>'90247'],
                ['postal_code'=>'90248'],
                ['postal_code'=>'90249'],
                ['postal_code'=>'90250'],
                ['postal_code'=>'90260'],
                ['postal_code'=>'90266'],
                ['postal_code'=>'90278'],
                ['postal_code'=>'90254'],
                ['postal_code'=>'90277'],
                ['postal_code'=>'90293'],
                ['postal_code'=>'90301'],
                ['postal_code'=>'90303'],
                ['postal_code'=>'90304'],
                ['postal_code'=>'90305'],
                ['postal_code'=>'90501'],
                ['postal_code'=>'90503'],
                ['postal_code'=>'90504'],
                ['postal_code'=>'90505'],
                ['postal_code'=>'90293'],
                ['postal_code'=>'90043'],
                ['postal_code'=>'90045'],
                ['postal_code'=>'90047'],
                ['postal_code'=>'90710'],
                ['postal_code'=>'90717'],
            ],
            3 => [
                ['postal_code'=>'90015'],
            ]
        ];

        foreach($regions as $zone_id => $regions)
        {
            $zone = Zone::find($zone_id);

            foreach($regions as $region) {
                $zone->regions()->save(new Region($region));
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
