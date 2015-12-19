<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContraintsToDiscountRegions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('discount_regions', function(Blueprint $table)
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
		Schema::table('discount_regions', function(Blueprint $table)
		{
			$table->dropForeign('discount_regions_discount_id_foreign');
		});
	}

}
