<?php

use App\ServiceCoords;
use App\Vehicle;
use App\Washer;
use App\User;
use App\Location;
use App\Service;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        $apiKey = \App::make('Chrisbjr\ApiGuard\Models\ApiKey');
        $apiKey->truncate();
        $apiLog = \App::make('Chrisbjr\ApiGuard\Models\ApiLog');
        $apiLog->truncate();

        User::truncate();
        Vehicle::truncate();
        Washer::truncate();
//        Location::truncate();
        Service::truncate();
        ServiceCoords::truncate();

		$this->call('UsersTableSeeder');
		$this->call('VehiclesTableSeeder');
		$this->call('WashersTableSeeder');
		$this->call('ServicesTableSeeder');
		$this->call('ServiceCoordsTableSeeder');

        $this->call('ApiKeysTableSeeder');

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
	}

}
