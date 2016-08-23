<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderSmsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->nullable()->index();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->integer('from')->unsigned()->nullable()->index();
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
            $table->integer('to')->unsigned()->nullable()->index();
            $table->foreign('to')->references('id')->on('users')->onDelete('cascade');
            $table->mediumText('message');
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
        Schema::drop('order_sms_logs');
    }
}
