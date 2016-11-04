<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddonServicePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addon_service', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('addon_id')->unsigned()->index();
            $table->foreign('addon_id')->references('id')->on('addons')->onDelete('cascade');
            $table->integer('service_id')->unsigned()->index();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->integer('price')->nullable();
            $table->integer('sequence')->index()->unsigned();
            $table->tinyInteger('is_active')->index()->unsigned()->default(1);
            $table->tinyInteger('is_corp')->index()->unsigned()->default(0);
//            $table->primary(['addon_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('addon_service');
    }
}
