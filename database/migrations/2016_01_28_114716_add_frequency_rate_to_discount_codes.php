<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFrequencyRateToDiscountCodes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('discount_codes', function(Blueprint $table)
		{
			$table->integer('frequency_rate')->unsigned()->default(0)->after('code');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('discount_codes', function(Blueprint $table)
		{
			$table->dropColumn('frequency_rate');
		});
	}

}
