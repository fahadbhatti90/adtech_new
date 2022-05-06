<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblAmsProfilesValidate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ams_profiles_validate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('profileId', 50);
            $table->string('name', 191);
            $table->string('countryCode', 50);
            $table->tinyInteger('isActive')->default(1);
            $table->date('creationDate');
            $table->tinyInteger('flag')->default(0);
            $table->string('reportDate', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ams_profiles_validate');
    }
}
