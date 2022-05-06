<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsAdgroupReportsDownloadedDataSpV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_adgroup_reports_downloaded_data_sp')) {
            Schema::table('tbl_ams_adgroup_reports_downloaded_data_sp', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_adgroup_reports_downloaded_data_sp', 'fkConfigId')) {
                    $table->bigInteger('fkConfigId')->default(1)->after('fkProfileId');
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
