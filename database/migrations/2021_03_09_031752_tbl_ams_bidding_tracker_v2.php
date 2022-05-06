<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsBiddingTrackerV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_bidding_tracker')) {
            Schema::table('tbl_ams_bidding_tracker', function (Blueprint $table) {
                $table->string('targetId',50)->default(0);
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
        if (Schema::hasTable('tbl_ams_bidding_tracker')) {
            Schema::table('tbl_ams_bidding_tracker', function (Blueprint $table) {
                $table->dropColumn(['targetId']);
            });
        }
    }
}
