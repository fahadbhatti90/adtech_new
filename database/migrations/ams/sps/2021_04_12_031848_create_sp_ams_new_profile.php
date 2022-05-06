<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsNewProfile extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSNewProfile");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSNewProfile`(in report_date varchar(50))
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
          
                
          SELECT
           profileId,
           countryCode,
           NAME,
           creationDate 
           FROM $primaryDb.`tbl_ams_profiles`
           WHERE DATE_FORMAT(LEFT(creationDate,10),\"%Y-%m-%d\") = DATE_FORMAT(report_date,\"%Y-%m-%d\")
        
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
              , 'spAMSNewProfile'
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
              , 'spAMSNewProfile'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSNewProfile");
    }
}
