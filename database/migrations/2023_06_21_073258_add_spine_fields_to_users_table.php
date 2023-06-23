<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpineFieldsToUsersTable extends Migration
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
            $table->string('sso_unid')->after('id')->nullable();
            $table->string('request_source')->after('sso_unid')->nullable();
            $table->string('user_type')->after('request_source')->nullable();
            $table->string('employee_id')->after('user_type')->nullable();
            $table->string('company')->after('name')->nullable();
            $table->string('location')->after('company')->nullable();
            $table->string('joining_date')->after('location')->nullable();
            $table->string('block_date')->after('joining_date')->nullable();
            $table->string('phone')->after('block_date')->nullable();
            $table->string('status')->after('phone')->nullable();
            $table->string('portal_id')->after('status')->nullable();
            $table->string('role_id')->after('portal_id')->nullable(); 

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
