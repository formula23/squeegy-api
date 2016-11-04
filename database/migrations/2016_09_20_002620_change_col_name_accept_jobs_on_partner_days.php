<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeColNameAcceptJobsOnPartnerDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `partner_days` CHANGE `accpeting_orders` `accepting_orders` SMALLINT(5)  UNSIGNED  NOT NULL  DEFAULT \'1\'');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `partner_days` CHANGE `accepting_orders` `accpeting_orders` SMALLINT(5)  UNSIGNED  NOT NULL  DEFAULT \'1\'');
    }
}
