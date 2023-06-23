<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('portal_id')->nullable();
            $table->string('role_id')->nullable();
            $table->string('assign_date')->nullable();
            $table->string('remove_date')->nullable();
            $table->string('remarks')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('updated_id')->nullable();



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
