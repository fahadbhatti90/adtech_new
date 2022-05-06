<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpForImpression extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $drop = "DROP procedure IF EXISTS spCalculateKeywordLevelBidMultipler";
        $sql = "CREATE PROCEDURE `spCalculateKeywordLevelBidMultipler`(IN campaign_id BIGINT,IN keyword_id TEXT)
        BEGIN
         SELECT 
			fkProfileId,
			campaignId,
			campaignName,
			keywordId,
			SUM(`impressions`) AS Impressions
			 FROM 
			`tbl_ams_keyword_reports_downloaded_data_sp`
			WHERE 
			`reportDate`> DATE_ADD(DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY), INTERVAL -2 WEEK) 
			AND reportDate < CURRENT_DATE
			AND campaignId = campaign_id
				AND FIND_IN_SET(keywordId , keyword_id) 
			GROUP BY 
			`fkProfileId`,
			campaignId,
			campaignName,
			keywordId
        ;
        END
        ";
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
        DB::connection("mysql")->unprepared("DROP procedure IF EXISTS spCalculateKeywordLevelBidMultipler");
    }
}
