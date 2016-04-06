<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTestSubscribeStatusToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `orders` CHANGE `status` `status` ENUM('test', 'request','confirm','receive','schedule','assign','enroute','start','done','cancel')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            \DB::statement("ALTER TABLE `orders` CHANGE `status` `status` ENUM('request','confirm','receive','schedule','assign','enroute','start','done','cancel')");
        });
    }
}
