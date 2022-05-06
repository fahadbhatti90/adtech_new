<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsTargetsReportsDownloadedDataSdForBidingRule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkBatchId');
            $table->bigInteger('fkAccountId');
            $table->string('fkProfileId', 50);
            $table->bigInteger('fkConfigId');
            $table->string('campaignId', 50);
            $table->string('adGroupId', 50);
            $table->string('targetId', 50);
            $table->longText('targetingText');
            $table->string('impressions', 50);
            $table->string('clicks', 50);
            $table->string('cost', 50);
            $table->string('currency', 50);
            $table->string('attributedConversions14d', 50);
            $table->string('attributedConversions14dSameSKU', 50);
            $table->string('attributedUnitsOrdered14d', 50);
            $table->string('attributedSales14d', 50);
            $table->string('attributedSales14dSameSKU', 50);
            $table->string('reportDate', 50);
            $table->date('creationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule');
    }
}
