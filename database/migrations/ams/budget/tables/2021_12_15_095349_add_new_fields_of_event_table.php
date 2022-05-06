<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsOfEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_ams_budget_rule_list', function (Blueprint $table) {
            $table->string('eventId', 100)->nullable()->after('ruleType');
            $table->string('metric', 10)->nullable()->change();
            $table->string('comparisonOperator', 30)->nullable()->change();
            $table->string('threshold', 8)->nullable()->change();
        });
        Schema::table('tbl_ams_budget_rule_list', function (Blueprint $table) {
            $table->string('eventName', 100)->nullable()->after('eventId');
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
            $table->dropColumn('eventId');
            $table->dropColumn('eventName');
        });
    }
}
