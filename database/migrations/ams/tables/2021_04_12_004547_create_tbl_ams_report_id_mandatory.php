<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsReportIdMandatory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_report_id_mandatory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('report_type_id', 100)->nullable();
            $table->bigInteger('total_report_id')->default(0);
            $table->string('reportDate', 50);
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
        Schema::dropIfExists('tbl_ams_report_id_mandatory');
    }
}
