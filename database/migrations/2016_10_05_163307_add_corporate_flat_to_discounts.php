<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCorporateFlatToDiscounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->tinyInteger('corporate_only')->default(0)->after('single_use_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('corporate_only');
        });
    }
}
