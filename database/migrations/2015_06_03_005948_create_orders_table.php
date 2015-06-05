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
            $table->integer('location_id')->unsigned()->index();
            $table->integer('washer_id')->unsigned()->index();
            $table->integer('service_id')->unsigned()->index();
            $table->string('job_number')->index();
            $table->enum('status', array('requested', 'processing', 'completed', 'cancelled'))->index()->default('requested');
            $table->text('instructions');
            $table->timestamp('en_route_at');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('number_photos');
            $table->float('price');
            $table->string('discount_code');
            $table->string('rating');
            $table->text('rating_comment');

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
