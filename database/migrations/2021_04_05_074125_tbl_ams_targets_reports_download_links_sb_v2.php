<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsTargetsReportsDownloadLinksSbV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_targets_reports_download_links_sb')) {
            Schema::table('tbl_ams_targets_reports_download_links_sb', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_targets_reports_download_links_sb', 'expiration')) {
                    $table->string('expiration')->default('NA');
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
        if (Schema::hasTable('tbl_ams_targets_reports_download_links_sb')) {
            Schema::table('tbl_ams_targets_reports_download_links_sb', function (Blueprint $table) {
                $table->dropColumn(['expiration']);
            });
        }
    }
}
