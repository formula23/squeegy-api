<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpAddressToEtalogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('eta_logs', function(Blueprint $table)
		{
			$table->string("ip_address")->nullable()->after('message');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('eta_logs', function(Blueprint $table)
		{
            $table->dropColumn('ip_address');
		});
	}

}
