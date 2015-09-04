<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveAppVersionToUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
            $table->tinyInteger('is_active')->after('remember_token')->unsigned()->index()->default(1);
            $table->string('app_version', 10)->after('is_active')->default('1.2')->index();
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
			$table->removeColumn('is_active');
			$table->removeColumn('app_version');
		});
	}

}
