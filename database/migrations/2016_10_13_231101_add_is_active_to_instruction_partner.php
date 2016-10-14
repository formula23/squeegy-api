<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveToInstructionPartner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruction_partner', function (Blueprint $table) {
            $table->tinyInteger('is_active')->after('sequence')->default(1)->unsgined()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instruction_partner', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
