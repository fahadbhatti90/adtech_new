<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsTacosKeywordidList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_tacos_keywordid_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fkId');
            $table->bigInteger('fkTacosId');
            $table->bigInteger('fkConfigId');
            $table->bigInteger('profileId');
            $table->string('reportType',50);
            $table->bigInteger('keywordId');
            $table->bigInteger('adGroupId');
            $table->bigInteger('campaignId');
            $table->string('keywordText');
            $table->string('matchType');
            $table->string('state');
            $table->decimal('bid', 8, 2);
            $table->string('servingStatus');
            $table->string('creationDate');
            $table->string('lastUpdatedDate');
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
        Schema::dropIfExists('tbl_ams_tacos_keywordid_list');
    }
}
