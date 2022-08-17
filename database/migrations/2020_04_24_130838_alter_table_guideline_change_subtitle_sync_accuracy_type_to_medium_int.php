<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableGuidelineChangeSubtitleSyncAccuracyTypeToMediumInt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->dropColumn('subtitleSyncAccuracy');
        });
        Schema::table('guideline', function (Blueprint $table) {
            $table->unsignedMediumInteger('subtitleSyncAccuracy')->after('maxCharsPerSecond')->nullable();
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
            //
        });
    }
}
