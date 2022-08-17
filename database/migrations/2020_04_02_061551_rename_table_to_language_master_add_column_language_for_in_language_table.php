<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTableToLanguageMasterAddColumnLanguageForInLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('language', 'language_master');
        Schema::table('language_master', function (Blueprint $table) {
            $table->tinyInteger('languageFor')->after('regionStandard')->comment('1 for transcript, 2 for translate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('language_master', 'language');
        Schema::table('language', function (Blueprint $table) {
            $table->dropColumn('languageFor');
        });
    }
}
