<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsLinkDuplication extends Migration
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
        $drop = "DROP procedure IF EXISTS spAMSLinkDuplication";
        $sql = "CREATE PROCEDURE `spAMSLinkDuplication`(in startdate varchar(50))
        BEGIN
          DECLARE max_date DATE DEFAULT NULL ;
          DECLARE `_rollback` BOOL DEFAULT 0 ;
          
          
          /*DECLARE EXIT HANDLER FOR SQLEXCEPTION 
          BEGIN
            -- ERROR
            SELECT 
              \"Syntax Error\" ;
            SET `_rollback` = 1 ;
            ROLLBACK ;
          END ;
          */
          
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
          
        
        DELETE FROM $primaryDb.`tbl_ams_link_duplication` where DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\");
          INSERT INTO $primaryDb.`tbl_ams_link_duplication`
        SELECT NULL, fkAccountId, 'Adgroup_SB_Links'  , STATUS,COUNT(*)
         AS cnt, reportDate
        FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sb`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Adgroup_SD_Links' , STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sd`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId,'Adgroup_SP_Links', STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_adgroup_reports_download_links_sp`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Campaign_SB_Links', STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sb`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Campaign_SD_Links', STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sd`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId,'Campaign_SP_Links', STATUS,COUNT(*)
         AS cnt ,reportDate
        FROM $primaryDb.`tbl_ams_campaigns_reports_download_links_sp`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId,'Keyword_SB_Links' , STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sb`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Keyword_SP_Links', STATUS,COUNT(*)
         AS cnt ,reportDate
        FROM $primaryDb.`tbl_ams_keyword_reports_download_links_sp`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Productads_Links' , STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_productsads_reports_download_links`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Productads_Links_SD' , STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_productsads_reports_download_links_sd`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId,'Target_Links', STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_targets_reports_download_links`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId,'Target_SB_Links',STATUS,COUNT(*)
         AS cnt,reportDate
        FROM $primaryDb.`tbl_ams_targets_reports_download_links_sb`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
        UNION
        SELECT NULL, fkAccountId, 'Target_SD_Links', STATUS,COUNT(*)
         AS cnt ,reportDate
        FROM $primaryDb.`tbl_ams_targets_reports_download_links_sd`
        GROUP BY STATUS,fkAccountId,reportDate
        HAVING cnt> 1 
         OR (cnt = 1 AND STATUS != 'SUCCESS')
        AND DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
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
              , 'spAMSLinkDuplication'
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
              , 'spAMSLinkDuplication'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSLinkDuplication");
    }
}
