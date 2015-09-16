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
            $table->tinyInteger('single_use_code')->after('frequency_rate')->unsigned()->default(0);
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
			$table->dropColumn('single_use_code');
		});
	}

}
