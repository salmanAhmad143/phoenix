<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableGuidelineAddForeignkeyForLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->foreign('languageId')->references('languageId')->on('language_master')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->dropForeign(['languageId']);
        });
    }
}
