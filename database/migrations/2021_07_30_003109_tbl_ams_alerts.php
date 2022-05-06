<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblAmsAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('alertName')->nullable();
            $table->bigInteger('fkAccountId')->nullable();
            $table->bigInteger('fkProfileId')->nullable();
            $table->boolean('dayPartingAlertsStatus')->default(0);
            $table->boolean('biddingRuleAlertsStatus')->default(0);
            $table->boolean('tacosAlertsStatus')->default(0);
            $table->mediumText('addCC');
            $table->bigInteger('createdBy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_alerts');
    }
}
