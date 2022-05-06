<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsDayPartingSchedulesTimeCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_day_parting_schedules_time_campaigns', function (Blueprint $table) {
            $table->bigInteger('fkScheduleId');
            $table->string('startTime', 100);
            $table->string('endTime', 100);
            $table->string('day', 12)
                ->comment('monday, tuesday, wednesday, thursday, friday, saturday, sunday');
            $table->date('creationDate', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_day_parting_schedules_time_campaigns');
    }
}
