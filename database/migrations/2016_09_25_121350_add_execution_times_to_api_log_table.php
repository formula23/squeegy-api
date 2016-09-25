<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExecutionTimesToApiLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->decimal('execution_start',15,5)->unsigned()->after('response_body');
            $table->decimal('execution_end',15,5)->unsigned()->after('execution_start');
            $table->decimal('execution_time',15,5)->unsigned()->after('execution_end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropColumn('execution_start');
            $table->dropColumn('execution_end');
            $table->dropColumn('execution_time');
        });
    }
}
