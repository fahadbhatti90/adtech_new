<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsScoreCard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_score_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('Total_Report_Id')->default(0);
            $table->bigInteger('Total_Link_Reports')->default(0);
            $table->bigInteger('New_Profile')->default(0);
            $table->bigInteger('Active_Profile')->default(0);
            $table->bigInteger('InActive_Profile')->default(0);
            $table->bigInteger('Profile_Incompatible_with_SD')->default(0);
            $table->bigInteger('Agency_Type')->default(0);
            $table->string('reportDate', 50)->nullable();
            $table->date('creationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_score_card');
    }
}
