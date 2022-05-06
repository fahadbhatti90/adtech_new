<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDayPartingCampaignPortfolioScheduleIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_ams_day_parting_campaign_schedule_ids', function ($table) {
            $table->dropColumn('mon');
            $table->dropColumn('tue');
            $table->dropColumn('wed');
            $table->dropColumn('thu');
            $table->dropColumn('fri');
            $table->dropColumn('sat');
            $table->dropColumn('sun');
            $table->dropColumn('startTime');
            $table->dropColumn('endTime');
        });

        Schema::table('tbl_ams_day_parting_portfolio_schedule_ids', function ($table) {
            $table->dropColumn('mon');
            $table->dropColumn('tue');
            $table->dropColumn('wed');
            $table->dropColumn('thu');
            $table->dropColumn('fri');
            $table->dropColumn('sat');
            $table->dropColumn('sun');
            $table->dropColumn('startTime');
            $table->dropColumn('endTime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
