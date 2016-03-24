<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoginLogoutToUserActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('washer_activity_logs', function (Blueprint $table) {
            $table->dateTime('login')->nullable()->after('user_id');
            $table->dateTime('logout')->nullable()->after('login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('washer_activity_logs', function (Blueprint $table) {
            $table->removeColumn('login');
            $table->removeColumn('logout');
        });
    }
}
