<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColsFromPartnerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->dropColumn('next_date');
            $table->dropColumn('time_start');
            $table->dropColumn('time_end');
            $table->dropColumn('order_cut_off_time');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->date('next_date')->after('partner_id');
            $table->string('time_start')->after('next_date');
            $table->string('time_end')->after('time_start');
            $table->string('order_cut_off_time')->after('time_end');
        });
    }
}
