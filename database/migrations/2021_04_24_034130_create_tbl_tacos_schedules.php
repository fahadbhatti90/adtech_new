<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblTacosSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_tacos_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profileId');
            $table->unsignedBigInteger('campaignId');
            $table->text('metric');
            $table->double('tacos',5,2)->default(0.00);
            $table->double('min',5,2)->default(0.00);
            $table->double('max',6,2)->default(0.00);
            $table->boolean('isActive')->default(1);
            $table->unsignedBigInteger('userID');
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
        Schema::dropIfExists('tbl_tacos_list');
    }
}
