<?php

use App\CancelReason;
use Illuminate\Database\Seeder;

class CancelReasons extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        CancelReason::truncate();

        $data = [
            ['description' => 'Customer not here'],
            ['description' => 'Unable to contact'],
            ['description' => 'Customer declined work'],
            ['description' => 'Location not accessible'],
            ['description' => 'Squeegy admin cancelled'],
        ];

        foreach($data as  $d){
            CancelReason::create($d);
        }



        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints
    }
}
