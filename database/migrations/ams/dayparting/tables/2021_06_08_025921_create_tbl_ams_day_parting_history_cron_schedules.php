<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsDayPartingHistoryCronSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_day_parting_history_cron_schedules', function (Blueprint $table) {
            $table->bigInteger('fkScheduleId');
            $table->string('startTime', 100)->nullable();
            $table->string('endTime', 100)->nullable();
                $table->string('isMessage', 255)->nullable();
            $table->date('cronDate');
            $table->dateTime('creationDate');
            $table->dateTime('updationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_day_parting_history_cron_schedules');
    }
}
