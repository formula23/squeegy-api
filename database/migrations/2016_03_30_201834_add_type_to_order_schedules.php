<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToOrderSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_schedules', function (Blueprint $table) {
            $table->enum('type', ['one-time', 'subscription'])->after('window_close')->default('one-time');
            $table->dateTime('window_close')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dateTime('window_close')->change();
        });
    }
}
