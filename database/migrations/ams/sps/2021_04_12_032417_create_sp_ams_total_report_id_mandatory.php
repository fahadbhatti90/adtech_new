<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsTotalReportIdMandatory extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalReportIDMandatory");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSTotalReportIDMandatory`()
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
              
            
            DELETE FROM $primaryDb.`tbl_ams_report_id_mandatory` where reportDate =DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") ;
              INSERT INTO $primaryDb.`tbl_ams_report_id_mandatory`
            SELECT NULL,'SD_Product_Ads',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
               AND countrycode !='MX'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'AdGroup_SD',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
               AND countrycode !='MX'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Campaign_SD',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
               AND countrycode !='MX'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Product_Targeting_SD',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND countrycode !='MX'
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Campaign_SP',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'AdGroup_SP',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
            
                UNION
            SELECT NULL,'Keyword_SP',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Product_Ads',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
            UNION
            SELECT NULL,'ASINs',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Product_Targeting',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
                UNION
            SELECT NULL,'Keyword_SB',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
            UNION
            SELECT NULL,'Campaign_SB',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
            UNION
            SELECT NULL,'AdGroup_SB',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
            UNION
            SELECT NULL,'Product_Targeting_SB',COUNT(total_report_id) AS cnt,
            DATE_FORMAT(CURRENT_DATE - INTERVAL 1 DAY, \"%Y%m%d\") AS report_date, 
            CURRENT_DATE AS creation_date   FROM
            (SELECT COUNT(a.id) AS total_report_id,COUNT(a.profileid) AS cnt
                FROM $primaryDb.`tbl_ams_profiles`a
            LEFT JOIN `tbl_account` b ON (a.id = b.fkid)
             WHERE  isActive = 1
             AND TYPE != 'agency'
                GROUP BY  profileid
                HAVING cnt= 1)c
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
              , 'spAMSTotalReportIDMandatory'
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
              , 'spAMSTotalReportIDMandatory'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalReportIDMandatory");
    }
}
