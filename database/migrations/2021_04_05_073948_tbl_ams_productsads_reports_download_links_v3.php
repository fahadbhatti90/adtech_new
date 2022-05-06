<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsProductsadsReportsDownloadLinksV3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_productsads_reports_download_links')) {
            Schema::table('tbl_ams_productsads_reports_download_links', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_productsads_reports_download_links', 'expiration')) {
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
        if (Schema::hasTable('tbl_ams_productsads_reports_download_links')) {
            Schema::table('tbl_ams_productsads_reports_download_links', function (Blueprint $table) {
                $table->dropColumn(['expiration']);
            });
        }
    }
}
