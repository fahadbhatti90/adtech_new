<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsBidMultiplierTrackerV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_bid_multiplier_tracker')) {
            Schema::table('tbl_ams_bid_multiplier_tracker', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'keywordId')) {
                    $table->bigInteger('keywordId')->after('campaignId')->default(0);
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
        if (Schema::hasTable('tbl_ams_bid_multiplier_tracker')) {
            Schema::table('tbl_ams_bid_multiplier_tracker', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_ams_bid_multiplier_tracker', 'keywordId')) {
                    $table->dropColumn('keywordId')->after('campaignId')->default(0);
                }
            });
        }
    }
}
