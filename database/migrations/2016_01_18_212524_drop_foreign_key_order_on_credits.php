<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropForeignKeyOrderOnCredits extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('credits', function(Blueprint $table)
		{
			$table->dropForeign('credits_order_id_foreign');
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
			$table->foreign('order_id')->references('id')->on('orders');
		});
	}

}
