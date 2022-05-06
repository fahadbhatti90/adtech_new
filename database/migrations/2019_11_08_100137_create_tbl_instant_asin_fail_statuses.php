<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblInstantAsinFailStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_asins_instant_fail_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('failed_data');
            $table->text('failed_reason');
            $table->string('failed_at', 50)->default('0000-00-00');
            $table->unsignedInteger('c_id')->unsigned();
            $table->boolean('isNew')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_asins_instant_fail_statuses');
    }
}
