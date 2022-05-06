<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsScoreCard extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSScoreCard");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSScoreCard`(in report_date varchar(50))
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
          delete from $primaryDb.`tbl_ams_score_card` where DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\");
            INSERT INTO $primaryDb.`tbl_ams_score_card`
          (
              id,
            Total_Report_Id ,
            Total_Link_Reports ,
            New_Profile ,
            Active_Profile ,
            InActive_Profile ,
            Profile_Incompatible_with_SD ,
            Agency_Type ,
            `reportDate` ,
            `creationDate`
          )
          (	
            SELECT
            NULL,
              ( SELECT 
                COUNT(*) AS Total_Report_Id
                FROM $primaryDb.`tbl_ams_report_id` 
                WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS Total_Report_Id, 
              (SELECT
                COUNT(*) AS Total_Link_Reports
            FROM (
            SELECT *
            FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sb`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sd`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sp`
            
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_asin_reports_download_links`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sb`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sd`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sp`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sb`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sp`
        
            UNION ALL
        
            SELECT * 
            FROM 
            $primaryDb.`tbl_ams_productsads_reports_download_links`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_productsads_reports_download_links_sd`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_targets_reports_download_links`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_targets_reports_download_links_sb`
        
            UNION ALL
        
            SELECT *
            FROM $primaryDb.`tbl_ams_targets_reports_download_links_sd`
            ) t
            WHERE DATE_FORMAT(t.reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS Total_Link_Reports,
             ( SELECT
                COUNT(profileid) AS New_Profile 
                FROM $primaryDb.`tbl_ams_profiles`
                WHERE DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\")=DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS New_Profile,
            (SELECT 
                COUNT(isActive) AS Active_Profile
                FROM $primaryDb.`tbl_ams_profiles`
                WHERE isActive = 1
                AND TYPE != 'agency'
                AND DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") <= DATE_FORMAT(report_date,\"%Y-%m-%d\")		
                ) AS Active_Profile,
            (SELECT 
                COUNT(isActive) AS InActive_Profile
                FROM $primaryDb.`tbl_ams_profiles`
                WHERE isActive = 0
                AND DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") <= DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS InActive_Profile,
            (SELECT 
                COUNT(CountryCode) AS  Profile_Incompatible_with_SD
                FROM $primaryDb.`tbl_ams_profiles`
                WHERE CountryCode = 'MX'
                AND TYPE != 'agency'
                AND DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") <=DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS Profile_Incompatible_with_SD,
            (SELECT 
                COUNT(TYPE) AS Agency_Type
                FROM $primaryDb.`tbl_ams_profiles`
                WHERE TYPE = 'agency'
                AND DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") <=DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS Agency_Type,
                (SELECT 
                 DISTINCT(reportDate)
                FROM $primaryDb.`tbl_ams_report_id`
                WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")) AS reportDate,
                
                Current_Date AS creationDate
                )
                
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
              , 'spAMSScoreCard'
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
              , 'spAMSScoreCard'
              , 'Commit'
              , CURRENT_TIMESTAMP()
             )
          ;
          
          COMMIT;
          END
          IF;
          
        END 
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSScoreCard");
    }
}
