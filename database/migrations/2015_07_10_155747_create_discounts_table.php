<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->default(0);
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['amt', 'pct'])->default('amt');
            $table->integer('amount');
            $table->string('code');
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->tinyInteger('new_customer')->default(0);
            $table->enum('scope', ['system','user'])->default('system');
            $table->integer('frequency_rate')->default(0);
            $table->tinyInteger('is_active')->default(0);
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
        Schema::drop('discounts');
    }
}
