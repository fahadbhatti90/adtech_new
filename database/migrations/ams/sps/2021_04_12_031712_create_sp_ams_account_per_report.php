<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateSpAmsAccountPerReport extends Migration
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
        $drop = "DROP procedure IF EXISTS spAMSAccountPerReport";
        $sql = "CREATE PROCEDURE `spAMSAccountPerReport`(in start_date varchar(50))
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
          
        delete from $primaryDb.`tbl_ams_report_id_error` where DATE_FORMAT(Report_date,\"%Y-%m-%d\") = DATE_FORMAT(start_date,\"%Y-%m-%d\");	
        INSERT INTO $primaryDb.`tbl_ams_report_id_error`
           SELECT
        NULL,
         t4.`id` AS Account_id
         ,t4.`fkId`AS Profile_id
        ,t3.reportType
        ,start_date
        FROM 
        (SELECT
        t2.`fkAccountId`,
        t2.`reportType`
        FROM
        (SELECT
        `profileID`,
        `reportType`,
        CONCAT(`profileID`,`reportType`) AS id1
        FROM 
        $primaryDb.`tbl_ams_report_id`
        WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(start_date,\"%Y-%m-%d\")
        GROUP BY 1,2,3
        )t1
        RIGHT JOIN
        (SELECT
        a.`fkAccountId`,
        a.`reportType`
        ,CONCAT(a.`profileID`,a.`reportType`) AS id2
        FROM 
        $primaryDb.`tbl_ams_report_id` a
        JOIN `tbl_ams_profiles` b
        ON a.`profileID`=b.`profileId`
        WHERE b.isActive = 1
        AND b.type !='agency'
        AND countrycode !='MX'
        AND DATE_FORMAT(LEFT(b.creationDate,10),\"%Y-%m-%d\") <=DATE_FORMAT(start_date,\"%Y-%m-%d\")
        GROUP BY 1,2,3
        )t2
        ON (t1.id1 = t2.id2)
        WHERE t1.id1 IS NULL)t3
        JOIN 
        (SELECT `id`,`fkId` FROM $primaryDb.`tbl_account`)t4
        ON t3.`fkAccountId`= t4.`id`
         UNION
         SELECT
        NULL,
         t4.`id` AS Account_id
         ,t4.`fkId`AS Profile_id
        ,t3.reportType
        ,start_date
        FROM 
        (SELECT
        t2.`fkAccountId`,
        t2.`reportType`
        FROM
        (SELECT
        `profileID`,
        `reportType`,
        CONCAT(`profileID`,`reportType`) AS id1
        FROM 
        $primaryDb.`tbl_ams_report_id`
        WHERE DATE_FORMAT(reportDate,\"%Y-%m-%d\") = DATE_FORMAT(start_date,\"%Y-%m-%d\")
        GROUP BY 1,2,3
        )t1
        RIGHT JOIN
        (SELECT
        a.`fkAccountId`,
        a.`reportType`
        ,CONCAT(a.`profileID`,a.`reportType`) AS id2
        FROM 
        $primaryDb.`tbl_ams_report_id` a
        JOIN `tbl_ams_profiles` b
        ON a.`profileID`=b.`profileId`
        WHERE b.isActive = 1
        AND b.type !='agency'
        AND DATE_FORMAT(LEFT(b.creationDate,10),\"%Y-%m-%d\") <=DATE_FORMAT(start_date,\"%Y-%m-%d\")
        GROUP BY 1,2,3
        )t2
        ON (t1.id1 = t2.id2)
        WHERE t1.id1 IS NULL)t3
        JOIN 
        (SELECT `id`,`fkId` FROM $primaryDb.`tbl_account`)t4
        ON t3.`fkAccountId`= t4.`id`
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
              , 'spAMSAccountPerReport'
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
              , 'spAMSAccountPerReport'
              , 'Commit'
              , CURRENT_TIMESTAMP()
             )
          ;
          
          COMMIT;
          END
          IF;
          END";
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spAMSAccountPerReport");
    }
}
