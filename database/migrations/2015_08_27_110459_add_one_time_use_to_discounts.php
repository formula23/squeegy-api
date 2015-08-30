<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOneTimeUseToDiscounts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('discounts', function(Blueprint $table)
		{
            $table->tinyInteger('one_time_use')->after('frequency_rate')->unsigned()->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('discounts', function(Blueprint $table)
		{
			$table->dropColumn('one_time_use');
		});
	}

}
