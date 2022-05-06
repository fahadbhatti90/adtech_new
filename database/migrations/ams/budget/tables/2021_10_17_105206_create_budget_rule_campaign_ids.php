<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetRuleCampaignIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_budget_rule_campaign_ids', function (Blueprint $table) {
            $table->integer('fkRuleId');
            $table->integer('fkCampaignId');
            $table->timestamp('createdAt');
            $table->boolean('shouldRemove')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_budget_rule_campaign_ids');
    }
}
