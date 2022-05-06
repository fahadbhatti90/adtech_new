<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTacosListActivityTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tacos_list_activity_tracker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fkTacosId');
            $table->unsignedBigInteger('profileId');
            $table->string('campaignId', 191);
            $table->text('metric');
            $table->double('tacos',5,2)->default(0.00);
            $table->double('min',5,2)->default(0.00);
            $table->double('max',6,2)->default(0.00);
            $table->boolean('isActive')->default(1);
            $table->unsignedBigInteger('userID');
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
        Schema::dropIfExists('tbl_tacos_list_activity_tracker');
    }
}
