<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableMediaTranscriptChangeMinMaxDurationColumnSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('media_transcript')) {
            Schema::table('media_transcript', function (Blueprint $table) {
                DB::statement('ALTER TABLE `media_transcript` CHANGE `minDuration` `minDuration` SMALLINT UNSIGNED NULL DEFAULT NULL, CHANGE `maxDuration` `maxDuration` SMALLINT UNSIGNED NULL DEFAULT NULL;');
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
        if (Schema::hasTable('media_transcript')) {
            Schema::table('media_transcript', function (Blueprint $table) {
                DB::statement('ALTER TABLE `media_transcript` CHANGE `minDuration` `minDuration` INT UNSIGNED NULL DEFAULT NULL, CHANGE `maxDuration` `maxDuration` INT UNSIGNED NULL DEFAULT NULL;');
            }); 
        }
    }
}
