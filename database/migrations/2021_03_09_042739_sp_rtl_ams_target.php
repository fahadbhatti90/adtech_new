<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SpRtlAmsTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $Db1 = \getDbAndConnectionName("db1");
        $Db2 = \getDbAndConnectionName("db2");
        $procedure = "
    CREATE PROCEDURE `spRTLAMSTarget`(IN `max_date` VARCHAR(20))
    BEGIN
    DECLARE `_rollback` BOOL DEFAULT 0 ;
  
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    -- ERROR
    SELECT 
      'Syntax Error' ;
    SET `_rollback` = 1 ;
    ROLLBACK ;
  END ;
  DECLARE EXIT HANDLER FOR SQLWARNING 
  BEGIN
    -- WARNING
    SELECT 
      'Warning by DB';
    SET `_rollback` = 1 ;
    ROLLBACK;
  END ;
  
  START TRANSACTION ;
  SET autocommit = 0 ;
  
DELETE FROM `" . $Db1 . "`.tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule WHERE reportDate = max_date;
INSERT INTO `" . $Db1 . "`.`tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule`
   ( `fkBatchId`
    , `fkAccountId`
    , `fkProfileId`
    , `fkConfigId`
    , `campaignId`
    , `campaignName`
    , `adGroupId`
    , `adGroupName`
    , `targetId`
    , `targetingText`
    , `impressions`
    , `clicks`
    , `cost`
    , `currency`
    , `attributedConversions14d`
    , `attributedConversions14dSameSKU`
    , `attributedUnitsOrdered14d`
    , `attributedSales14d`
    , `attributedSales14dSameSKU`
    , `reportDate`
    , `creationDate`
   )
   (
          SELECT
           `fkBatchId`
         , `fkAccountId`
         , `fkProfileId`
         , `fkConfigId`
         , `campaignId`
         , `campaignName`
         , `adGroupId`
         , `adGroupName`
         , `targetId`
         , `targetingText`
         , `impressions`
         , `clicks`
         , `cost`
         , `currency`
         , `attributedConversions14d`
         , `attributedConversions14dSameSKU`
         , `attributedUnitsOrdered14d`
         , `attributedSales14d`
         , `attributedSales14dSameSKU`
         , `reportDate`
         , CURRENT_DATE AS `creationDate`

      FROM `" . $Db1 . "`.`tbl_ams_targets_reports_downloaded_data_sd`
      WHERE
         reportDate = max_date
   )
;
 
  IF `_rollback` THEN
  INSERT INTO `" . $Db2 . "`.`etl_logs`
     ( `log_id`
      , `Stored_Procedure_Name`
      , `Execution_status`
      , `LogDate`
     )
     VALUES
     ( NULL
      , 'spRTLAMSTarget'
      , 'RollBack'
      , CURRENT_TIMESTAMP()
     )
  ;
  
  ROLLBACK;
  ELSE
  INSERT INTO `" . $Db2 . "`.`etl_logs`
     ( `log_id`
      , `Stored_Procedure_Name`
      , `Execution_status`
      , `LogDate`
     )
     VALUES
     ( NULL
      , 'spRTLAMSTarget'
      , 'Commit'
      , CURRENT_TIMESTAMP()
     )
  ;
  
  COMMIT;
  END
  IF;
    END
";
        DB::unprepared("DROP procedure IF EXISTS spRTLAMSTarget");
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP procedure IF EXISTS spRTLAMSTarget");
    }
}
