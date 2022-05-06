<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsAlertsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_alerts')) {
            Schema::table('tbl_ams_alerts', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_alerts', 'bidMultiplierAlertsStatus')) {
                    $table->boolean('bidMultiplierAlertsStatus')->after('tacosAlertsStatus')->default(0);
                }
                if (!Schema::hasColumn('tbl_ams_alerts', 'budgetMultiplierAlertsStatus')) {
                    $table->boolean('budgetMultiplierAlertsStatus')->after('tacosAlertsStatus')->default(0);
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
        if (Schema::hasTable('tbl_ams_alerts')) {
            Schema::table('tbl_ams_alerts', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_ams_alerts', 'bidMultiplierAlertsStatus')) {
                    $table->dropColumn(['bidMultiplierAlertsStatus']);
                }
                if (Schema::hasColumn('tbl_ams_alerts', 'budgetMultiplierAlertsStatus')) {
                    $table->dropColumn(['budgetMultiplierAlertsStatus']);
                }
            });
        }
    }
}
