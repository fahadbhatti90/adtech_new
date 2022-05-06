<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_budget_rule_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ruleName','355');
            $table->unsignedBigInteger('fkProfileId')->default(0);
            $table->string('adType', 20)->nullable();
            $table->string('ruleType', 20)->nullable();
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->string('recurrence', 10);
            $table->boolean('mon')->default(0);
            $table->boolean('tue')->default(0);
            $table->boolean('wed')->default(0);
            $table->boolean('thu')->default(0);
            $table->boolean('fri')->default(0);
            $table->boolean('sat')->default(0);
            $table->boolean('sun')->default(0);
            $table->string('daysOfWeek', 50)->nullable();
            $table->string('metric',10);
            $table->string('comparisonOperator',30);
            $table->integer('threshold');
            $table->integer('raiseBudget');
            $table->boolean('isActive')->default(1);
            $table->boolean('apiStatus')->default(0)->comment('0: api call did not happen  or failed, 1 : success');
            $table->json('apiMsg')->nullable();
            $table->unsignedBigInteger('userID')->default(0);
            $table->string('ruleId', 60)->nullable();
            $table->string('ruleState', 60)->nullable();
            $table->string('ruleStatus', 60)->nullable();
            $table->string('ruleStatusDetails', 60)->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->string('createdDate', 20)->nullable();
            $table->string('lastUpdatedDate', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_budget_rule_list');
    }
}
