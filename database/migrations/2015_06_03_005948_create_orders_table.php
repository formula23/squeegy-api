<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('worker_id')->unsigned()->nullable()->index();
            $table->integer('service_id')->unsigned()->index();
            $table->integer('vehicle_id')->unsigned()->index();
            $table->string('job_number')->index()->nullable();
            $table->enum('status', array('request', 'confirm', 'cancel', 'enroute', 'start', 'done'))->index()->default('request');
            $table->integer('eta')->unsigned()->nullable();
            $table->text('location');
            $table->text('instructions')->nullable();
            $table->timestamp('confirm_at')->nullable();
            $table->timestamp('enroute_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('cancel_at')->nullable();
            $table->string('number_photos')->nullable();
            $table->integer('price')->nullable()->unsigned();
            $table->integer('discount')->nullable()->unsigned();
            $table->integer('charged')->nullable()->unsigned();
            $table->string('promo_code')->nullable();
            $table->string('rating')->nullable();
            $table->text('rating_comment')->nullable();

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
		Schema::drop('orders');
	}

}
