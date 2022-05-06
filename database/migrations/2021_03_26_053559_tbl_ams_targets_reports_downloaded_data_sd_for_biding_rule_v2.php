<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsTargetsReportsDownloadedDataSdForBidingRuleV2 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule')) {
			Schema::table('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
				$table->string('campaignName')->default('NA');
				$table->string('adGroupName')->default('NA');
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
		if (Schema::hasTable('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule')) {
			if (Schema::hasColumn('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', 'campaignName')) {
				Schema::table('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
					$table->dropColumn('campaignName');
				});
			}

			if (Schema::hasColumn('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', 'adGroupName')) {
				Schema::table('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
					$table->dropColumn('adGroupName');
				});
			}

			if (Schema::hasColumn('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', 'targetingExpression')) {
				Schema::table('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
					$table->dropColumn('targetingExpression');
				});
			}

			if (Schema::hasColumn('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', 'targetingType')) {
				Schema::table('tbl_ams_targets_reports_downloaded_data_sd_for_biding_rule', function (Blueprint $table) {
					$table->dropColumn('targetingType');
				});
			}
		}
	}
}
