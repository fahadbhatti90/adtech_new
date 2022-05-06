<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsTargetsReportsDownloadedDataSdv2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_targets_reports_downloaded_data_sd')) {
            Schema::table('tbl_ams_targets_reports_downloaded_data_sd', function (Blueprint $table) {
                $table->string('campaignName')->default('NA');
                $table->string('adGroupName')->default('NA');
                $table->string('targetingExpression')->default('NA');
                $table->string('targetingType')->default('NA');
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
        if (Schema::hasTable('tbl_ams_targets_reports_downloaded_data_sd')) {
            Schema::table('tbl_ams_targets_reports_downloaded_data_sd', function (Blueprint $table) {
                $table->dropColumn(['campaignName','adGroupName','targetingExpression','targetingType']);
            });
        }
    }
}
