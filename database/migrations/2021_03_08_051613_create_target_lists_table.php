<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_bidding_rule_target_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkId');
            $table->bigInteger('fkBiddingRuleId');
            $table->bigInteger('fkConfigId');
            $table->bigInteger('profileId');
            $table->string('reportType',50);
            $table->bigInteger('campaignId');
            $table->bigInteger('adGroupId');
            $table->bigInteger('targetId');
            $table->string('state');
            $table->decimal('bid', 8, 2);
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
        Schema::dropIfExists('tbl_ams_bidding_rule_target_list');
    }
}
