<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveForeignKeyFromApilogs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_logs', function(Blueprint $table)
		{
			$table->dropForeign('api_logs_user_id_foreign');
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
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

}
