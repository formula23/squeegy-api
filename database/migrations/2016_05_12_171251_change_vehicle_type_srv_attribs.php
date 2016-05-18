<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVehicleTypeSrvAttribs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_attribs', function(Blueprint $table)
        {
            \DB::statement("ALTER TABLE `service_attribs` CHANGE `vehicle_type` `vehicle_type` ENUM('Car','Truck','SUV','Van','Minivan') not null default 'Car'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_attribs', function(Blueprint $table)
        {
            \DB::statement("ALTER TABLE `service_attribs` CHANGE `vehicle_type` `vehicle_type` ENUM('Car','Non-Car') not null default 'Car'");
        });
    }
}
