<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeStatusOnCredits extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('credits', function(Blueprint $table)
		{
			\DB::statement("ALTER TABLE `credits` CHANGE `status` `status` ENUM('','auth','capture','void') not null default ''");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('credits', function(Blueprint $table)
		{
			\DB::statement("ALTER TABLE `credits` CHANGE `status` `status` ENUM('auth','capture','void') not null default 'auth'");
		});
	}

}
