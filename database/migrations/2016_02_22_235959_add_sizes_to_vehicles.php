<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizesToVehicles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vehicles', function(Blueprint $table)
		{
			\DB::statement("ALTER TABLE `vehicles` DROP `type`");
			$table->enum('type', ['Car', 'Truck', 'SUV', 'Van', 'Minivan'])->after('color')->nullable()->default('Car');
			$table->enum('size', ['Compact', 'Midsize', 'Large'])->after('type')->nullable()->default('Midsize');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vehicles', function(Blueprint $table)
		{
			\DB::statement("ALTER TABLE `vehicles` DROP `size`");
			\DB::statement("ALTER TABLE `vehicles` DROP `type`");
			$table->string('type')->nullable()->after('color');
		});
	}

}
