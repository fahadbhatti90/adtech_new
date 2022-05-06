<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsBidMultiplierCronV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_bid_multiplier_cron')) {
            Schema::table('tbl_ams_bid_multiplier_cron', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'checkRule')) {
                    $table->boolean('checkRule')->after('runStatus')->default(0);
                }
                if (!Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'ruleResult')) {
                    $table->boolean('ruleResult')->after('checkRule')->default(0);

                }
                if (!Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'isData')) {
                    $table->boolean('isData')->after('ruleResult')->default(0);
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
        if (Schema::hasTable('tbl_ams_bid_multiplier_cron')) {
            Schema::table('tbl_ams_bid_multiplier_cron', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'checkRule')) {
                    $table->dropColumn('checkRule')->after('runStatus')->default(0);
                }
                if (Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'ruleResult')) {
                    $table->dropColumn('ruleResult')->after('runStatus')->default(0);
                }
                if (Schema::hasColumn('tbl_ams_bid_multiplier_cron', 'isData')) {
                    $table->dropColumn('isData')->after('checkRule')->default(0);
                }
            });
        }
    }
}
