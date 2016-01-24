<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToApiLogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_logs', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->nullable()->index()->after('api_key_id');
			$table->foreign('user_id')->references('id')->on('users');
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
			$table->dropColumn('user_id');
		});
	}

}
