<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsLinkError extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSLinkError");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSLinkError`(in report_date varchar(50))
        BEGIN
          DECLARE max_date DATE DEFAULT NULL ;
          DECLARE `_rollback` BOOL DEFAULT 0 ;
          
          
          DECLARE EXIT HANDLER FOR SQLEXCEPTION 
          BEGIN
            -- ERROR
            SELECT 
              \"Syntax Error\" ;
            SET `_rollback` = 1 ;
            ROLLBACK ;
          END ;
          
          
         /* DECLARE EXIT HANDLER FOR SQLWARNING 
          BEGIN
            -- WARNING
            SELECT 
              \"Warning by DB\" ;
            SET `_rollback` = 1 ;
            ROLLBACK;
          END ;
          
        */ 
         
         
          START TRANSACTION ;
          
         
          SET autocommit = 0 ;
          delete from $primaryDb.`tbl_ams_link_error` where DATE_FORMAT(Report_date,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\");
         
        INSERT INTO tbl_ams_link_error
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'AdGroup_SB'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'AdGroup_SD'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'AdGroup_SP'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_asin_reports_download_links` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'ASINs'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Campaign_SB'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Campaign_SD'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Campaign_SP'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Keyword_SB'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Keyword_SP'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_productsads_reports_download_links` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Product_Ads'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_productsads_reports_download_links_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'SD_Product_Ads'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_download_links` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Product_Targeting'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_download_links_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Product_Targeting_SB'
        UNION
        SELECT NULL,`fkAccountId`,`profileID`,`reportType`,`reportDate` FROM $primaryDb.`tbl_ams_report_id` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_download_links_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        )
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") AND reportType = 'Product_Targeting_SD'
        ORDER BY 2
        ;
          
            IF `_rollback` THEN
          INSERT INTO $secondaryDb.`etl_logs`
             ( `log_id`
              , `Stored_Procedure_Name`
              , `Execution_status`
              , `LogDate`
             )
             VALUES
             ( NULL
              , 'spAMSLinkError'
              , 'RollBack'
              , CURRENT_TIMESTAMP()
             )
          ;
          
          ROLLBACK;
          ELSE
          INSERT INTO $secondaryDb.`etl_logs`
             ( `log_id`
              , `Stored_Procedure_Name`
              , `Execution_status`
              , `LogDate`
             )
             VALUES
             ( NULL
              , 'spAMSLinkError'
              , 'Commit'
              , CURRENT_TIMESTAMP()
             )
          ;
          
          COMMIT;
          END
          IF;
          
        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP procedure IF EXISTS spAMSLinkError");
    }
}
