<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('notification_id')->unsigned()->nullable()->index();
            $table->foreign('notification_id')->references('id')->on('notifications');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('order_id')->unsigned()->nullable()->index();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->string('message');
            $table->enum('delivery_method', ['push','sms'], 'push');
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
        Schema::drop('notification_logs');
    }
}
