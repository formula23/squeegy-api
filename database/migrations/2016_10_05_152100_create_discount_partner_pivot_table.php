<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountPartnerPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_partner', function (Blueprint $table) {
            $table->integer('discount_id')->unsigned()->index();
            $table->foreign('discount_id')->references('id')->on('discounts')->onDelete('cascade');
            $table->integer('partner_id')->unsigned()->index();
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->primary(['discount_id', 'partner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('discount_partner');
    }
}
