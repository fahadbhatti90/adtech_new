<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblAmsBudgetRuleDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_ams_budget_rule_list', function (Blueprint $table) {
            $table->string('threshold', 8)->default(0.00)->nullable()->change();
            $table->string('raiseBudget', 8)->default(0.00)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_ams_budget_rule_list', function (Blueprint $table) {

            $table->integer('threshold');
            $table->integer('raiseBudget');
        });
    }
}
