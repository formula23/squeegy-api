<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToPartnerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->smallInteger('accpeting_orders')->unsigned()->after('order_cap')->default(1);
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
            $table->drop('accpeting_orders');
        });
    }
}
