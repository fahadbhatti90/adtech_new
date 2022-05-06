<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpAMSTacos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $primaryDb = DB::connection("mysql")->getDatabaseName();
        $drop = "DROP PROCEDURE IF EXISTS spAMSTacos";
        $sql = "CREATE PROCEDURE `spAMSTacos`(
				   IN campaign_id BIGINT,
				   IN id TEXT,
				   IN reporttype VARCHAR(10),
				   IN numofdays INT,
				   IN report VARCHAR(30))
        BEGIN
            IF report ='keyword' THEN
              CALL $primaryDb.`spCalculateKeywordTacos`( campaign_id , id ,  reporttype ,  numofdays );
            ELSEIF report = 'target' THEN
              CALL $primaryDb.`spCalculateTargetTacos`( campaign_id , id ,  numofdays );
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
        DB::connection("mysql")->unprepared("DROP PROCEDURE IF EXISTS `spAMSTacos`");
    }
}
