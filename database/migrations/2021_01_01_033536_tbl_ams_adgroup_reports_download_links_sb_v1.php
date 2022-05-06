<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsAdgroupReportsDownloadLinksSbV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_adgroup_reports_download_links_sb')) {
            Schema::table('tbl_ams_adgroup_reports_download_links_sb', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_adgroup_reports_download_links_sb', 'fkConfigId')) {
                    $table->bigInteger('fkConfigId')->default(1)->after('profileID');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
