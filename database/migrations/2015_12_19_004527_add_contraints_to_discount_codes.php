<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContraintsToDiscountCodes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('discount_codes', function(Blueprint $table)
		{
			$table->foreign('discount_id')->references('id')->on('discounts');
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
            $table->dropForeign('discount_codes_discount_id_foreign');
		});
	}

}
