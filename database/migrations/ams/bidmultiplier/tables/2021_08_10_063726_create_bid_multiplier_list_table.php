<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBidMultiplierListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_bid_multiplier_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profileId');
            $table->string('campaignId', 191);
            $table->string('bid',10)->nullable();
            $table->boolean('isActive')->default(1);
            $table->unsignedBigInteger('userID');
            $table->date('startDate');
            $table->date('endDate');
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_bid_multiplier_list');
    }
}
