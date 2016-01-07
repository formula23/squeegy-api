<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeviceInfoColsToApiLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_logs', function(Blueprint $table)
		{

			$table->string('device')->after('ip_address')->nullable();
			$table->string('device_os')->after('device')->nullable();
			$table->string('device_carrier')->after('device_os')->nullable();
			$table->string('device_identifier')->after('device_carrier')->nullable();
			$table->string('app_type')->after('device_identifier')->nullable();
			$table->string('app_version')->after('app_type')->nullable();

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
			$table->dropColumn('device');
			$table->dropColumn('device_os');
			$table->dropColumn('device_carrier');
			$table->dropColumn('device_identifier');
			$table->dropColumn('app_type');
			$table->dropColumn('app_version');
		});
	}

}
