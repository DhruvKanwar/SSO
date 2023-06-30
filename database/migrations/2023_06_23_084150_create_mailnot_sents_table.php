<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailnotSentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailnot_sents', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('emp_email')->nullable();
            $table->longText('mail_response')->nullable();
            $table->string('mail_date')->nullable();
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
        Schema::dropIfExists('mailnot_sents');
    }
}
