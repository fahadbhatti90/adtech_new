<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsBidMultiplierCron extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_bid_multiplier_cron', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkMultiplierId');
            $table->bigInteger('profileId');
            $table->bigInteger('fkConfigId');
            $table->bigInteger('campaignId');
            $table->string('type',50);
            $table->string('sponsoredType',50);
            $table->boolean('isActive')->default(false);
            $table->boolean('runStatus')->default(false);
            $table->string('currentRunTime',50);
            $table->string('lastRunTime',50);
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_bid_multiplier_cron');
    }
}
