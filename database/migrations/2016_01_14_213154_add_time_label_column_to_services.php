<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeLabelColumnToServices extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('services', function(Blueprint $table)
		{
			$table->string('time_label')->nullable()->after('time');
			$table->integer('sequence')->unsigned()->after('time_label')->default(0);
			$table->tinyInteger('is_active')->unsigned()->after('sequence')->default(1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('services', function(Blueprint $table)
		{
			$table->dropColumn('time_label');
			$table->dropColumn('sequence');
			$table->dropColumn('is_active');
		});
	}

}
