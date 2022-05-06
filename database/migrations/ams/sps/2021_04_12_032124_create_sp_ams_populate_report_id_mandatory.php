<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsPopulateReportIdMandatory extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateReportIdMandatory");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSPopulateReportIdMandatory`(in start_date varchar(50))
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
            `report_type_id`,
            `total_report_id`
            FROM $primaryDb.`tbl_ams_report_id_mandatory`
            where DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(start_date,\"%Y-%m-%d\")
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
              , 'spAMSPopulateReportIdMandatory'
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
              , 'spAMSPopulateReportIdMandatory'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateReportIdMandatory");
    }
}
