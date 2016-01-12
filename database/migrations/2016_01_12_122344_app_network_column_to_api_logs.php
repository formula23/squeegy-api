<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppNetworkColumnToApiLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_logs', function(Blueprint $table)
		{
			$table->string('network_type')->after('device_identifier')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_logs', function(Blueprint $table)
		{
			$table->dropColumn('network_type');
		});
	}

}
