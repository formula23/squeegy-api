<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructionPartnerPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruction_partner', function (Blueprint $table) {
            $table->integer('instruction_id')->unsigned()->index();
            $table->foreign('instruction_id')->references('id')->on('instructions')->onDelete('cascade');
            $table->integer('partner_id')->unsigned()->index();
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->string('label')->nullable();
            $table->string('hint')->nullable();
            $table->smallInteger('prepopulate')->unsigned()->nullable();
            $table->smallInteger('required')->unsigned()->nullable();
            $table->smallInteger('min_length')->unsigned()->nullable();
            $table->smallInteger('max_length')->unsigned()->nullable();
            $table->string('validation')->nullable();
            $table->string('validation_error_msg')->nullable();
            $table->integer('sequence')->default(0)->unsigned();
            $table->primary(['instruction_id', 'partner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('instruction_partner');
    }
}
