<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
//        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

		$this->call('ServiceCoordsTableSeeder');
		$this->call('ZoneRegionsTableSeeder');
		$this->call('PartnerTableSeeder');
		
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
	}

}
