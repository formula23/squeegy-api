<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstructionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruction_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('instruction_id')->unsigned()->index();
            $table->foreign('instruction_id')->references('id')->on('instructions')->onDelete('cascade');
            $table->string('option');
            $table->string('value');
            $table->integer('sequence')->default(0)->unsigned();
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
        Schema::drop('instruction_options');
    }
}
