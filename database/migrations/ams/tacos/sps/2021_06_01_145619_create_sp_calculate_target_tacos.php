<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpCalculateTargetTacos extends Migration
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
        $drop = "DROP PROCEDURE IF EXISTS spCalculateTargetTacos";
        $sql = "CREATE PROCEDURE `spCalculateTargetTacos`(IN campaign_id BIGINT, IN target_id TEXT, IN numofdays INT)
        BEGIN
        SELECT
            `fkAccountId`,
            `fkProfileId`,
            -- `profile_name`,
            `campaignId`,
            `campaignName`,
            `adGroupId`,
            `adGroupName`,
            `targetId`,
            `targetingText`,
            SUM(`impressions`) AS impression,
            SUM(`clicks`) AS clicks,
            SUM(`cost`) AS cost,
            SUM(`attributedConversions14d`) AS attributedConversions,
            SUM(`attributedConversions14dSameSKU`) AS attributedConversionsSameSKU,
            SUM(`attributedUnitsOrdered14d`) AS attributedUnitsOrdered,
            SUM(`attributedSales14d`) AS revenue,
            SUM(`attributedSales14dSameSKU`) AS attributedSalesSameSKU,
            
            CASE
            WHEN SUM(`impressions`) > 0
            THEN ROUND(SUM(`clicks`) / SUM(`impressions`), 2)
            ELSE 0
            END AS ctr,
            CASE
            WHEN SUM(`attributedSales14d`) > 0
            THEN ROUND( SUM(`cost`) / SUM(`attributedSales14d`), 2)
            ELSE 0
            END AS `acos`,
            CASE
            WHEN SUM(`clicks`) > 0
            THEN ROUND(SUM(`cost`) / SUM(`clicks`), 2)
            ELSE 0.00
            END AS cpc,
            CASE
            WHEN SUM(`cost`) > 0
            THEN ROUND(SUM(`attributedSales14d`) / SUM(`cost`) , 2)
            ELSE 0.00
            END AS roas,
            
            CASE
            WHEN SUM(`attributedUnitsOrdered14d`) > 0
            THEN ROUND(SUM(`cost`) / SUM(`attributedUnitsOrdered14d`), 2)
            ELSE 0.00
            END AS cpa
            FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule`
            WHERE `reportDate` >= DATE_SUB(CURRENT_DATE, INTERVAL numofdays DAY)
            AND campaignId = campaign_id
            AND FIND_IN_SET(targetId , target_id)
            GROUP BY
            `fkAccountId`,
            `fkProfileId`,
            -- `profile_name`,
            `campaignId`,
             `campaignName`,
            `adGroupId`,
            `adGroupName`,
            `targetId`,
            targetingText;
            -- `keywordText`,
            -- `matchType` ;
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
        DB::connection("mysql")->unprepared("DROP PROCEDURE IF EXISTS spCalculateTargetTacos");
    }
}
