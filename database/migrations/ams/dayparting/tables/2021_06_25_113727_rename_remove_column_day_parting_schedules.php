<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameRemoveColumnDayPartingSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_ams_day_parting_pf_campaign_schedules', function ($table) {
            $table->dropColumn('mon');
            $table->dropColumn('tue');
            $table->dropColumn('wed');
            $table->dropColumn('thu');
            $table->dropColumn('fri');
            $table->dropColumn('sat');
            $table->dropColumn('sun');
            $table->renameColumn('startTime', 'startDate');
            $table->renameColumn('endTime', 'endDate');
            $table->json('selectionHours');
        });

        Schema::table('tbl_ams_day_parting_pf_campaign_schedules', function ($table) {
            $table->string('startDate', 20)->nullable()->change();
            $table->string('endDate', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_ams_day_parting_pf_campaign_schedules', function ($table) {
            $table->boolean('mon')->default(0);
            $table->boolean('tue')->default(0);
            $table->boolean('wed')->default(0);
            $table->boolean('thu')->default(0);
            $table->boolean('fri')->default(0);
            $table->boolean('sat')->default(0);
            $table->boolean('sun')->default(0);
            $table->renameColumn('startDate', 'startTime');
            $table->renameColumn('endDate', 'endTime');
            $table->dropColumn('selectionHours');
        });
    }
}
