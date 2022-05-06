<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CampaignListBidMultiple extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_campaign_list')) {
            Schema::table('tbl_ams_campaign_list', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_campaign_list', 'bidMultiplier')) {
                    $table->bigInteger('bidMultiplier')->after('bidOptimization')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_ams_campaign_list')) {
            Schema::table('tbl_ams_campaign_list', function (Blueprint $table) {
                $table->dropColumn(['bidMultiplier']);
            });
        }
    }
}
