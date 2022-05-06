
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsReportIdHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_report_id_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkBatchId');
            $table->bigInteger('fkAccountId');
            $table->bigInteger('profileID');
            $table->bigInteger('fkConfigId');
            $table->string('reportId');
            $table->string('recordType', 50);
            $table->string('reportType', 100);
            $table->string('status');
            $table->string('statusDetails', 50);
            $table->string('reportDate', 20);
            $table->integer('isDone')->default(0);
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
        Schema::dropIfExists('tbl_ams_report_id_history');
    }
}
