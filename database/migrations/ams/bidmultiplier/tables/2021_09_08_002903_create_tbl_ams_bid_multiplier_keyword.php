<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsBidMultiplierKeyword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_bid_multiplier_keyword', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkConfigId');
            $table->bigInteger('fkMultiplierId');
            $table->bigInteger('profileId');
            $table->string('reportType',50);
            $table->bigInteger('keywordId');
            $table->bigInteger('adGroupId');
            $table->bigInteger('campaignId');
            $table->string('keywordText');
            $table->string('matchType');
            $table->string('state');
            $table->decimal('bid', 8, 2);
            $table->decimal('tempBid', 8, 2);
            $table->string('servingStatus');
            $table->string('creationDate');
            $table->string('lastUpdatedDate');
            $table->boolean('isEligible');
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
        Schema::dropIfExists('tbl_ams_bid_multiplier_keyword');
    }
}
