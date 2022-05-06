<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSpAmsPopulateDataDuplication extends Migration
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateDataDuplication");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spAMSPopulateDataDuplication`(in start_date varchar(50))
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
            `Report_Type_Data`,
            `Repetitive_Count`,
            reportDate
            FROM 
            $primaryDb.`tbl_ams_data_duplication`
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
              , 'spAMSPopulateDataDuplication'
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
              , 'spAMSPopulateDataDuplication'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSPopulateDataDuplication");
    }
}
