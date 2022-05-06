<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsTotalReportId extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalReportID");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSTotalReportID`(in startdate varchar(50))
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
            DELETE FROM $primaryDb.`tbl_ams_total_report_id` WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\");
            INSERT INTO $primaryDb.`tbl_ams_total_report_id`
            SELECT 
            NULL,
            reportType AS report_type_id , 
            COUNT(*) AS total_report_id, 
            reportDate ,
            CURRENT_DATE
            FROM $primaryDb.`tbl_ams_report_id`
             WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(startdate,\"%Y-%m-%d\")
             GROUP BY reportType ,
            reportDate
            ORDER BY 3 ASC
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
                  , 'spAMSTotalReportID'
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
                  , 'spAMSTotalReportID'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSTotalReportID");
    }
}
