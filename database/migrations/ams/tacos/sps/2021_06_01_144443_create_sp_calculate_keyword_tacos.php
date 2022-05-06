<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpCalculateKeywordTacos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $primaryDb = DB::connection("mysql")->getDatabaseName();
        $secondaryDb = DB::connection("mysqlDb2")->getDatabaseName();
        $drop = "DROP PROCEDURE IF EXISTS spCalculateKeywordTacos";
        $sql = "CREATE PROCEDURE `spCalculateKeywordTacos`(IN campaign_id BIGINT,IN keyword_id TEXT, IN reporttype VARCHAR(10), IN numofdays INT)
        BEGIN
         SELECT
          `fkAccountId`,
          `fkProfileId`,
          `profile_name`,
          `campaignId`,
          `campaignName`,
          `adGroupId`,
          `adGroupName`,
          `keywordId`,
          `keywordText`,
          `matchType`,
          SUM(`impressions`) AS impression,
          SUM(`clicks`) AS clicks,
          SUM(`cost`) AS cost,
          SUM(`attributedConversions`) AS attributedConversions,
          SUM(`attributedConversionsSameSKU`) AS attributedConversionsSameSKU,
          SUM(`attributedUnitsOrdered`) AS attributedUnitsOrdered,
          SUM(`attributedSales`) AS  revenue,
          SUM(`attributedSalesSameSKU`) AS attributedSalesSameSKU,
            CASE
                WHEN  SUM(`impressions`) > 0 
                THEN ROUND(SUM(`clicks`) /  SUM(`impressions`), 2) 
                ELSE 0 
              END AS ctr,
              CASE
                WHEN SUM(`attributedSales`)  > 0 
                THEN ROUND( SUM(`cost`) / SUM(`attributedSales`), 2) 
                ELSE 0 
              END AS `acos`,
              CASE
                WHEN SUM(`clicks`) > 0 
                THEN ROUND(SUM(`cost`) / SUM(`clicks`), 2) 
                ELSE 0.00 
              END AS cpc,
              CASE
                WHEN SUM(`cost`)  > 0 
                THEN ROUND(SUM(`attributedSales`) / SUM(`cost`) , 2) 
                ELSE 0.00 
              END AS roas,
              CASE
                WHEN SUM(`attributedUnitsOrdered`) > 0 
                THEN ROUND(SUM(`cost`) / SUM(`attributedUnitsOrdered`), 2) 
                ELSE 0.00 
              END AS cpa
        FROM $primaryDb.`tbl_rtl_ams_keyword`
        WHERE `reportDate` >= DATE_SUB(CURRENT_DATE, INTERVAL numofdays DAY)
        AND campaignId = campaign_id
        AND FIND_IN_SET(keywordId , keyword_id)
        AND report_type = reporttype
        GROUP BY 
          `fkAccountId`,
          `fkProfileId`,
          `profile_name`,
          `campaignId`,
          `campaignName`,
          `adGroupId`,
          `adGroupName`,
          `keywordId`,
          `keywordText`,
          `matchType` ;
        END";
        DB::connection("mysql")->unprepared($drop);
        DB::connection("mysql")->unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection("mysql")->unprepared("DROP PROCEDURE IF EXISTS spCalculateKeywordTacos");
    }
}
