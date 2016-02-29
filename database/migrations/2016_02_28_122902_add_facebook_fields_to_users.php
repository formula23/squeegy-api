<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFacebookFieldsToUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->string('age_range', 50)->nullable()->after('facebook_id');
			$table->string('birthday', 50)->nullable()->after('age_range');
			$table->string('gender', 50)->nullable()->after('birthday');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropColumn('age_range');
			$table->dropColumn('birthday');
			$table->dropColumn('gender');
		});
	}

}
