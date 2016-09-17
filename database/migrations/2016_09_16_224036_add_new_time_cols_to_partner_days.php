<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTimeColsToPartnerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->dateTime('open')->after('order_cut_off_time')->nullable();
            $table->dateTime('close')->after('open')->nullable();
            $table->dateTime('cutoff')->after('close')->nullable();
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
            $table->dropColumn('open');
            $table->dropColumn('close');
            $table->dropColumn('cutoff');
        });
    }
}
