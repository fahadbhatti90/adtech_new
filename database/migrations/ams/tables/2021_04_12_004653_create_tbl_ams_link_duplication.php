<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsLinkDuplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_link_duplication', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('Account_Id', 100)->nullable();
            $table->string('Report_Type_Link', 100)->nullable();
            $table->string('Status', 100)->nullable();
            $table->bigInteger('Repetitive_Count')->default(0);
            $table->string('reportDate', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_link_duplication');
    }
}
