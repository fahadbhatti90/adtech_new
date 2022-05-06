<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsProfileValidate extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSProfileValidate");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSProfileValidate`(IN report_date VARCHAR(50))
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
          Delete FROm  $primaryDb.`tbl_ams_profiles_validate` where DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") ;
          INSERT INTO $primaryDb.`tbl_ams_profiles_validate`
             ( 
                `id`,
                `profileId`,
                `name`,
                `countryCode`,
                `isActive`,
                `creationDate`,
                `flag`,
                reportDate
            )
             (
                SELECT
                NULL,
                profileId, 
                `name`,
                `countryCode`,
                `isActive`,
                `creationDate`,
                CASE
                WHEN DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\") THEN  1
                ELSE  0
                END AS flag,
                report_date
                    FROM
                $primaryDb.`tbl_ams_profiles`
                ORDER BY 6 DESC  
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
              , 'spAMSProfileValidate'
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
              , 'spAMSProfileValidate'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSProfileValidate");
    }
}
