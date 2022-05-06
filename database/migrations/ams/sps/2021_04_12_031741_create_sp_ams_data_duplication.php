<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsDataDuplication extends Migration
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
        $drop = "DROP procedure IF EXISTS spAMSDataDuplication";
        $sql = "CREATE PROCEDURE `spAMSDataDuplication`(in startdate varchar(50))
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
          
          Delete from $primaryDb.`tbl_ams_data_duplication` where reportDate = startdate;
            Insert into $primaryDb.`tbl_ams_data_duplication`	
            SELECT
            NULL,
             fkAccountId,
             'Adgroup SB Data Duplication',
             COUNT(*) AS reptitve_count,
             reportDate
              FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,adgroupId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sb` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL,fkAccountId,'Adgroup SB Data Not Present In Table',COUNT(*)AS reptitve,reportDate FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sb` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Adgroup SD Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,adgroupId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sd`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Adgroup SD Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sd` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL ,fkAccountId,'Adgroup SP Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,adgroupId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sp`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Adgroup SP Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sp` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_adgroup_reports_downloaded_data_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Asin Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,adgroupId,keywordId,ASIN,otherAsin,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_asin_reports_downloaded_sp`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") 
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId,keywordId,ASIN,otherAsin
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Asin Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_asin_reports_download_links` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_asin_reports_downloaded_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT Null, fkAccountId,'Campaign SB Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sb`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Campaign SB Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sb` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Campaign SD Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sd`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL,fkAccountId,'Campaign SD Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sd` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL,fkAccountId,'Campaign SP Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sp`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Campaign SP Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sp` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_campaigns_reports_downloaded_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL,fkAccountId,'Keyword SB Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,adgroupId,keywordId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_keyword_reports_downloaded_data_sb`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId,keywordId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Keyword SB Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sb` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_keyword_reports_downloaded_data_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Keyword SP Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,adgroupId,keywordId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_keyword_reports_downloaded_data_sp`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId,keywordId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Keyword SP Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sp` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_keyword_reports_downloaded_data_sp` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL,fkAccountId,'Productads Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT '`tbl_ams_productsads_reports_downloaded_data`', fkAccountId ,fkProfileId,campaignId,adgroupId,adId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_productsads_reports_downloaded_data`
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId,adId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL,fkAccountId,'Productads Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_productsads_reports_download_links` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_productsads_reports_downloaded_data` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL,fkAccountId,'Productads SD Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT  fkAccountId ,fkProfileId,campaignId,adgroupId,ASIN,sku,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_productsads_reports_downloaded_data_sd`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") 
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,adgroupId,ASIN,sku
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Productads SD Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_productsads_reports_download_links_sd` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_productsads_reports_downloaded_data_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Target Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,targetId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") 
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,targetId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL, fkAccountId,'Target Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_targets_reports_download_links` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT NULL, fkAccountId,'Target SB Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,targetId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data_sb`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") 
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,targetId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT NULL,fkAccountId,'Target SB Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_targets_reports_download_links_sb` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data_sb` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        UNION
            SELECT Null,fkAccountId,'Target SD Data Duplication',COUNT(*) AS reptitve_count,reportDate FROM
            (SELECT fkAccountId ,fkProfileId,campaignId,targetId,reportDate, COUNT(fkaccountId) AS cnt
            FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data_sd`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") 
            GROUP BY fkAccountId,reportDate,fkProfileId,campaignId,targetId
            HAVING cnt> 1)A 
            GROUP BY fkAccountId,reportDate
            UNION
            SELECT Null,fkAccountId,'Target SD Data Not Present In Table',COUNT(*)AS reptitve_count,reportDate FROM $primaryDb.`tbl_ams_targets_reports_download_links_sd` WHERE fkAccountId NOT IN
        (
        SELECT fkAccountId FROM $primaryDb.`tbl_ams_targets_reports_downloaded_data_sd` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        ) AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\") AND fileSize > 22
        GROUP BY   fkAccountId,reportDate
        
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
              , 'spAMSDataDuplication'
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
              , 'spAMSDataDuplication'
              , 'Commit'
              , CURRENT_TIMESTAMP()
             )
          ;
          
          COMMIT;
          END
          IF;
          END
        ";

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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSDataDuplication");
    }
}
