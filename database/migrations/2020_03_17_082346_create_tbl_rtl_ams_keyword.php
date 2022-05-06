<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRtlAmsKeyword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_rtl_ams_keyword', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fkBatchId');
            $table->integer('fkAccountId');
            $table->bigInteger('fkProfileId');
            $table->string('profile_name',100);
            $table->bigInteger('campaignId');
            $table->string('campaignName',191);
            $table->bigInteger('adGroupId');
            $table->string('adGroupName',191);
            $table->bigInteger('keywordId');
            $table->string('keywordText',191);
            $table->string('matchType',50);
            $table->string('report_type',5);
            $table->integer('impressions');
            $table->integer('clicks');
            $table->decimal('cost', 19, 2);
            $table->integer('attributedConversions');
            $table->integer('attributedConversionsSameSKU');
            $table->integer('attributedUnitsOrdered');
            $table->decimal('attributedSales', 19, 2);
            $table->decimal('attributedSalesSameSKU', 19, 2);
            $table->integer('reportDate');
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
        Schema::dropIfExists('tbl_rtl_ams_keyword');
    }
}
