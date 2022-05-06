<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsTotalLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_total_link', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('report_type_link', 100)->nullable();
            $table->bigInteger('total_link_count')->default(0);
            $table->string('reportDate', 50)->nullable();
            $table->date('creationDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_total_link');
    }
}
