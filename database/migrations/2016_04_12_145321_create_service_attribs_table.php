<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceAttribsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_attribs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned()->nullable()->index();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->enum('vehicle_type', ['Car','Non-Car'])->default('Car');
            $table->enum('vehicle_size', ['Compact','Midsize','Large'])->default('Midsize');
            $table->integer('etc')->unsigned()->nullable();
            $table->integer('surcharge')->unsigned()->nullable();
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
        Schema::drop('service_attribs');
    }
}
