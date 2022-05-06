<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsKeywordForBidingRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_keyword_for_biding_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fkBatchId');
            $table->integer('fkAccountId');
            $table->bigInteger('fkProfileId');
            $table->string('profile_name');
            $table->bigInteger('campaignId');
            $table->string('campaignName');
            $table->bigInteger('adGroupId');
            $table->string('adGroupName');
            $table->bigInteger('keywordId');
            $table->string('keywordText');
            $table->string('matchType',50);
            $table->string('report_type',5);
            $table->integer('impressions');
            $table->integer('clicks');
            $table->decimal('cost',19,2);
            $table->integer('attributedConversions');
            $table->integer('attributedConversionsSameSKU');
            $table->integer('attributedUnitsOrdered');
            $table->decimal('attributedSales',19,2);
            $table->decimal('attributedSalesSameSKU',19,2);
            $table->integer('reportDate');
            $table->datetime('creationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_keyword_for_biding_rule');
    }
}
