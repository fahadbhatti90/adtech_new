<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmsProfileV9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_ams_profiles')) {
            Schema::table('tbl_ams_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_ams_profiles', 'SponsoredDisplayTargetSixtyDays')) {
                    $table->tinyInteger('SponsoredDisplayTargetSixtyDays')->default(0);
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
        if (Schema::hasTable('tbl_ams_profiles')) {
            Schema::table('tbl_ams_profiles', function (Blueprint $table) {
                $table->dropColumn(['SponsoredDisplayTargetSixtyDays']);
            });
        }
    }
}
