<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSegmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_segments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable()->index();
			$table->foreign('user_id')->references('id')->on('users');
			$table->integer('segment_id')->unsigned()->nullable()->index();
			$table->foreign('segment_id')->references('id')->on('segments');
			$table->timestamp('subscriber_at')->nullable();
			$table->timestamp('user_at')->nullable();
			$table->timestamp('customer_at')->nullable();
			$table->timestamp('repeat_customer_at')->nullable();
			$table->timestamp('advocate_at')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_segments');
	}

}
