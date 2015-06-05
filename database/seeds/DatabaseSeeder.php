<?php

use App\Vehicle;
use App\Washer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        Vehicle::truncate();
        Washer::truncate();

		Model::unguard();

		$this->call('VehiclesTableSeeder');
		$this->call('WashersTableSeeder');
		$this->call('ServicesTableSeeder');
		$this->call('LocationsTableSeeder');
	}

}
