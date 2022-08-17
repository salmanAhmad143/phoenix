<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceProviderModelToLanguageMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('language_master', function (Blueprint $table) {
            $table->char('serviceProvider',20)->default('Google')->after('languageFor');

            $table->char('model',50)->default('default')->after('serviceProvider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('language_master', function (Blueprint $table) {
            $table->dropColumn('serviceProvider');
            $table->dropColumn('model');
        });
    }
}
