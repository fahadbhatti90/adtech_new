<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsPopulateReportIdError extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateReportIdError");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSPopulateReportIdError`(in start_date varchar(50))
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
            `Account_Id`,
            `Profile_id`,
            `Report_Type`,
            Report_date
            FROM 
            $primaryDb.`tbl_ams_report_id_error`
            where DATE_FORMAT(Report_date,\"%Y-%m-%d\") = DATE_FORMAT(start_date,\"%Y-%m-%d\")
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
              , 'spAMSPopulateReportIdError'
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
              , 'spAMSPopulateReportIdError'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateReportIdError");
    }
}
