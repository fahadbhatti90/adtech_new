<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BrandAssociation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_brand_association')) {
            Schema::table('tbl_brand_association', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_brand_association', 'sendEmail')) {
                    $table->boolean('sendEmail')->after('fkManagerId')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_brand_association')) {
            Schema::table('tbl_brand_association', function (Blueprint $table) {
                $table->dropColumn(['sendEmail']);
            });
        }
    }
}
