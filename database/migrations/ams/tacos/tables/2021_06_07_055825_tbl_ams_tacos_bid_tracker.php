<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsTacosBidTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_tacos_bid_tracker', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('fkTacosId');
            $table->bigInteger('fkConfigId');
            $table->string('profileId',50);
            $table->string('adGroupId',50);
            $table->string('campaignId',50);
            $table->string('state');
            $table->string('reportType',10);
            $table->string('oldBid');
            $table->string('bid');
            $table->string('keywordId',50);
            $table->string('targetId',50)->default(0);
            $table->string('code');
            $table->dateTime('creationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_tacos_bid_tracker');
    }
}
