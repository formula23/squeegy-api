<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instructions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->string('hint')->nullable();
            $table->enum('type', ['string','integer'])->default('string');
            $table->enum('input_type', ['text','textarea','radio','dropdown','multiselect'])->default('text');
            $table->smallInteger('prepopulate')->unsigned()->default(1);
            $table->smallInteger('required')->unsigned()->default(1);
            $table->smallInteger('min_length')->unsigned()->default(1);
            $table->smallInteger('max_length')->unsigned()->default(30);
            $table->string('validation')->nullable();
            $table->string('validation_error_msg')->nullable();
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
        Schema::drop('instructions');
    }
}
