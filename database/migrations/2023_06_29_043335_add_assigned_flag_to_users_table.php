<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedFlagToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('official_email')->after('email');
            $table->string('user_assigned')->default(0)->comment("1=>Yes 0=>No")->after('role_id');
            $table->string('user_ip')->after('user_assigned');
            $table->string('mail_sent')->default(0)->comment("1=>Yes 0=>No")->after('user_ip');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
