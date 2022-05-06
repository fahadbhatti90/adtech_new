<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTacosCron extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tacos_cron', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkTacosId');
            $table->bigInteger('profileId');
            $table->bigInteger('fkConfigId');
            $table->bigInteger('campaignId');
            $table->string('type',50);
            $table->string('sponsoredType',50);
            $table->string('lookBackPeriodDays',50);
            $table->string('frequency',50);
            $table->boolean('isActive')->default(false);
            $table->boolean('isData')->default(false);
            $table->boolean('runStatus')->default(false);
            $table->boolean('checkRule')->default(false);
            $table->boolean('ruleResult')->default(false);
            $table->string('currentRunTime',50);
            $table->string('lastRunTime',50);
            $table->string('nextRunTime',50);
            $table->boolean('emailSent')->default(false);
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_tacos_cron');
    }
}
