<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsCampaignsReportsDownloadedSdv2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_campaigns_reports_downloaded_sd')) {
            Schema::table('tbl_ams_campaigns_reports_downloaded_sd', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_ams_campaigns_reports_downloaded_sd', 'attributedUnitsSold14d')) {
                    $table->dropColumn('attributedUnitsSold14d');
                }
                if (Schema::hasColumn('tbl_ams_campaigns_reports_downloaded_sd', 'attributedDPV14d')) {
                    $table->dropColumn('attributedDPV14d');
                }
                if (Schema::hasColumn('tbl_ams_campaigns_reports_downloaded_sd', 'campaignStatus')) {
                    $table->dropColumn('campaignStatus');
                }
                $table->string('attributedConversions1d',50)->default(0);
                $table->string('attributedConversions7d',50)->default(0);
                $table->string('attributedConversions14d',50)->default(0);
                $table->string('attributedConversions30d',50)->default(0);
                $table->string('attributedConversions1dSameSKU',50)->default(0);
                $table->string('attributedConversions7dSameSKU',50)->default(0);
                $table->string('attributedConversions14dSameSKU',50)->default(0);
                $table->string('attributedConversions30dSameSKU',50)->default(0);
                $table->string('attributedUnitsOrdered1d',50)->default(0);
                $table->string('attributedUnitsOrdered7d',50)->default(0);
                $table->string('attributedUnitsOrdered14d',50)->default(0);
                $table->string('attributedUnitsOrdered30d',50)->default(0);
                $table->string('attributedSales1d',50)->default(0);
                $table->string('attributedSales7d',50)->default(0);
                $table->string('attributedSales30d',50)->default(0);
                $table->string('attributedSales1dSameSKU',50)->default(0);
                $table->string('attributedSales7dSameSKU',50)->default(0);
                $table->string('attributedSales14dSameSKU',50)->default(0);
                $table->string('attributedSales30dSameSKU',50)->default(0);
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
        if (Schema::hasTable('tbl_ams_campaigns_reports_downloaded_sd')) {
            Schema::table('tbl_ams_campaigns_reports_downloaded_sd', function (Blueprint $table) {
                $table->dropColumn(['attributedConversions1d',
                    'attributedConversions7d',
                    'attributedConversions14d',
                    'attributedConversions30d',
                    'attributedConversions1dSameSKU',
                    'attributedConversions7dSameSKU',
                    'attributedConversions14dSameSKU',
                    'attributedConversions30dSameSKU',
                    'attributedUnitsOrdered1d',
                    'attributedUnitsOrdered7d',
                    'attributedUnitsOrdered14d',
                    'attributedUnitsOrdered30d',
                    'attributedSales1d',
                    'attributedSales7d',
                    'attributedSales30d',
                    'attributedSales1dSameSKU',
                    'attributedSales7dSameSKU',
                    'attributedSales14dSameSKU',
                    'attributedSales30dSameSKU']);
            });
        }
    }
}
