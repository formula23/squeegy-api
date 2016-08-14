<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderCapFieldToPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->integer('order_cap', false, true)->after('frequency')->default(0);
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
            $table->dropColumn('order_cap');
        });
    }
}
