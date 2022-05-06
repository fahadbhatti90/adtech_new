<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblTacosSchedulesV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_tacos_list')) {
            Schema::table('tbl_tacos_list', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_tacos_list', 'campaignId')) {
                    $table->string('campaignId')->change();
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
