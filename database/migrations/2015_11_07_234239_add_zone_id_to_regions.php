<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddZoneIdToRegions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('regions', function(Blueprint $table)
		{
			$table->integer('zone_id')->nullable()->unsigned()->after('id');
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('regions', function(Blueprint $table)
		{
			$table->dropColumn('zone_id');
		});
	}

}
