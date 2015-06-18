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
            $table->integer('washer_id')->unsigned()->nullable()->index();
            $table->integer('service_id')->unsigned()->index();
            $table->integer('vehicle_id')->unsigned()->index();
            $table->string('job_number')->index()->nullable();
            $table->enum('status', array('pending', 'confirm', 'cancel', 'enroute', 'in-progress', 'done'))->index()->default('pending');
            $table->integer('lead_time')->unsigned()->nullable();
            $table->text('location');
            $table->text('instructions')->nullable();
            $table->timestamp('en_route_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('number_photos')->nullable();
            $table->integer('price')->nullable()->unsigned();
            $table->string('discount_code')->nullable();
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
