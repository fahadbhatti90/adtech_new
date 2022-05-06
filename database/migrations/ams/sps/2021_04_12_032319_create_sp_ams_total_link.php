<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsTotalLink extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalLink");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSTotalLink`(in startdate varchar(50))
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
            Delete from $primaryDb.`tbl_ams_total_link` where  DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\");
            INSERT INTO  $primaryDb.`tbl_ams_total_link`
            SELECT NULL, 
            'SD_Product_Ads', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_productsads_reports_download_links_sd` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT
             NULL, 'AdGroup_SD', COUNT(*) AS total_link,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_adgroup_reports_download_links_sd` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT 
            NULL,
            'Campaign_SD', COUNT(*) AS total_link,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_campaigns_reports_download_links_sd` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate
            UNION
            SELECT NULL,'Product_Targeting_SD', COUNT(*) AS total_link_count ,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_targets_reports_download_links_sd` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT 
            NULL,
            'Campaign_SP', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE  FROM  $primaryDb.`tbl_ams_campaigns_reports_download_links_sp` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate
            UNION
            SELECT 
            NULL,
            'AdGroup_SP', 
            COUNT(*)AS total_link,
            reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_adgroup_reports_download_links_sp` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate
            UNION
            SELECT NULL ,
            'Keyword_SP', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_keyword_reports_download_links_sp` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate  
             UNION
            SELECT
            NULL, 
            'Product_Ads', COUNT(*) AS total_link_count , reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_productsads_reports_download_links` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT NULL,
            'ASINs', COUNT(*) AS total_link_count, reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_asin_reports_download_links` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT
            NULL, 
            'Product_Targeting', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE   FROM   $primaryDb.`tbl_ams_targets_reports_download_links`
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate 
            UNION
            SELECT NULL, 'Keyword_SB', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE   FROM  $primaryDb.`tbl_ams_keyword_reports_download_links_sb` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate  
            UNION
            SELECT  NULL,
            'Campaign_SB', COUNT(*) AS total_link,reportDate,
            CURRENT_DATE  
             FROM  $primaryDb.`tbl_ams_campaigns_reports_download_links_sb` 
             WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
             GROUP BY reportDate 
            UNION
            SELECT 
            NULL,
            'AdGroup_SB',
            COUNT(*) AS total_link_count,
            reportDate,
            CURRENT_DATE  
            FROM  $primaryDb.`tbl_ams_adgroup_reports_download_links_sb`
             WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
             GROUP BY reportDate 
            UNION
            SELECT NULL,
            'Product_Targeting_SB', COUNT(*) AS total_link_count,reportDate,
            CURRENT_DATE 
              FROM  $primaryDb.`tbl_ams_targets_reports_download_links_sb` 
            WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
            GROUP BY reportDate    ; 
        
              IF `_rollback` THEN
              INSERT INTO $secondaryDb.`etl_logs`
                 ( `log_id`
                  , `Stored_Procedure_Name`
                  , `Execution_status`
                  , `LogDate`
                 )
                 VALUES
                 ( NULL
                  , 'spAMSTotalLink'
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
                  , 'spAMSTotalLink'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalLink");
    }
}
