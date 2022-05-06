<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpMasterHealthDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $primary = DB::connection("mysql")->getDatabaseName();
        $secondaryDb = DB::connection("mysqlDb2")->getDatabaseName();
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spMasterHealthDashboard");
        DB::connection("mysql")->unprepared("CREATE PROCEDURE `spMasterHealthDashboard`(in `max_date` varchar(20))
        BEGIN
        	
            DECLARE `_rollback` BOOL DEFAULT 0;
            DECLARE EXIT HANDLER FOR SQLEXCEPTION
            BEGIN
                -- ERROR
                SELECT \"Syntax Error\" ;
                SET `_rollback` = 1 ;
                ROLLBACK ;
            END;
            -- BEGIN
                -- WARNING
            --	SELECT \"Warning by DB\";
            --	SET `_rollback` = 1 ;
            --	ROLLBACK;
            -- END;
            START TRANSACTION;
            
            SET autocommit = 0;
                        
            CALL $primary.spAMSScoreCard(max_date);
            CALL $primary.spAMSProfileValidate(max_date);
            CALL $primary.spAMSTotalReportIDMandatory(); 
            CALL $primary.spAMSTotalReportID(max_date);
            CALL $primary.spAMSTotalLink(max_date);
            CALL $primary.spAMSLinkDuplication(max_date);
            CALL $primary.`spAMSDataDuplication`(max_date);
            CALL $primary.`spAMSAccountPerReport`(max_date); 
            CALL $primary.spAMSLinkError(`max_date`);
            
            
        IF `_rollback` THEN
        INSERT INTO $secondaryDb.`etl_logs`
           ( `log_id`
            ,`Stored_Procedure_Name`
            ,`Execution_status`
            ,`LogDate`
           )
           VALUES
           ( NULL
            ,'spMasterHealthDashboard'
            ,'RollBack'
            , CURRENT_TIMESTAMP()
           )
        ;
        ROLLBACK;
        ELSE
        INSERT INTO $secondaryDb.`etl_logs`
           ( `log_id`
            ,`Stored_Procedure_Name`
            ,`Execution_status`
            ,`LogDate`
           )
           VALUES
           ( NULL
            ,'spMasterHealthDashboard'
            ,'Commit'
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spMasterHealthDashboard");
    }
}
