<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsBidMultiplierTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_bid_multiplier_tracker', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('fkMultiplierId');
            $table->bigInteger('fkConfigId');
            $table->string('profileId',50);
            $table->string('campaignId',50);
            $table->string('bidOptimizationValue');
            $table->string('oldBid');
            $table->string('bid');
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
        Schema::dropIfExists('tbl_ams_bid_multiplier_tracker');
    }
}
