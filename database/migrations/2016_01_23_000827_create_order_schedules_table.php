<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderSchedulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_schedules', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('order_id')->unsigned()->nullable()->index();
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			$table->dateTime('window_open');
			$table->dateTime('window_close');
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
		Schema::drop('order_schedules');
	}

}
