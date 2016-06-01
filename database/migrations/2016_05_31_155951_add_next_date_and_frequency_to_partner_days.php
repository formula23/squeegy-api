<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNextDateAndFrequencyToPartnerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_days', function (Blueprint $table) {
            $table->date('next_date')->after('day_of_week');
            $table->enum('frequency', ['weekly','bi-weekly','monthly'])->after('time_end')->default('bi-weekly');
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
            $table->dropColumn('next_date');
            $table->dropColumn('frequency');
        });
    }
}
