<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsLinkError extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_link_error', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('Account_id')->default(0);
            $table->bigInteger('Profile_id')->default(0);
            $table->string('Report_Type', 100)->nullable();
            $table->string('Report_date', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_link_error');
    }
}
