<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponseToApiLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->string('host')->after('user_id')->nullable();
            $table->string('path')->after('host')->nullable();
            $table->text('request_body')->after('params')->nullable();
            $table->smallInteger('status_code')->unsigned()->after('request_body');
            $table->text('response_body')->after('status_code')->nullable();
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
            $table->dropColumn('host');
            $table->dropColumn('path');
            $table->dropColumn('request_body');
            $table->dropColumn('status_code');
            $table->dropColumn('response_body');
        });
    }
}
