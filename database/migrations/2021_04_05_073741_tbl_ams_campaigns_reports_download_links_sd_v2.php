<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsCampaignsReportsDownloadLinksSdV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_campaigns_reports_download_links_sd')) {
            Schema::table('tbl_ams_campaigns_reports_download_links_sd', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_campaigns_reports_download_links_sd', 'expiration')) {
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
        if (Schema::hasTable('tbl_ams_campaigns_reports_download_links_sd')) {
            Schema::table('tbl_ams_campaigns_reports_download_links_sd', function (Blueprint $table) {
                $table->dropColumn(['expiration']);
            });
        }
    }
}
