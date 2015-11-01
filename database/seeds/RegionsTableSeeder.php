<?php

use App\Region;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class RegionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Region::truncate();

        $regions = [
            ['postal_code'=>'90272'],
            ['postal_code'=>'90049'],
            ['postal_code'=>'90025'],
            ['postal_code'=>'90024'],
            ['postal_code'=>'90067'],
            ['postal_code'=>'90025'],
            ['postal_code'=>'90210'],
            ['postal_code'=>'90211'],
            ['postal_code'=>'90212'],
            ['postal_code'=>'90069'],
            ['postal_code'=>'90048'],
            ['postal_code'=>'90046'],
            ['postal_code'=>'90028'],
            ['postal_code'=>'90038'],
            ['postal_code'=>'90004'],
            ['postal_code'=>'90077'],
            ['postal_code'=>'90020'],
            ['postal_code'=>'90010'],
            ['postal_code'=>'90005'],
            ['postal_code'=>'90019'],
            ['postal_code'=>'90035'],
            ['postal_code'=>'90036'],
            ['postal_code'=>'90016'],
            ['postal_code'=>'90034'],
            ['postal_code'=>'90064'],
            ['postal_code'=>'90232'],
            ['postal_code'=>'90230'],
            ['postal_code'=>'90066'],
            ['postal_code'=>'90094'],
            ['postal_code'=>'90045'],
            ['postal_code'=>'90293'],
            ['postal_code'=>'90292'],
            ['postal_code'=>'90291'],
            ['postal_code'=>'90401'],
            ['postal_code'=>'90402'],
            ['postal_code'=>'90403'],
            ['postal_code'=>'90404'],
            ['postal_code'=>'90405'],
        ];

        Region::insert($regions);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
