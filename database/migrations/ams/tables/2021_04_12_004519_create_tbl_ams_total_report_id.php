<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsTotalReportId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_total_report_id', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('report_type_id', 100)->nullable();
            $table->bigInteger('total_report_id')->default(0);
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
        Schema::dropIfExists('tbl_ams_total_report_id');
    }
}
