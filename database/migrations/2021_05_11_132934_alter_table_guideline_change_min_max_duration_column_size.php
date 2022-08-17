<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableGuidelineChangeMinMaxDurationColumnSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('guideline')) {
            Schema::table('guideline', function (Blueprint $table) {
                DB::statement('ALTER TABLE `guideline` CHANGE `minDuration` `minDuration` SMALLINT UNSIGNED NULL DEFAULT NULL, CHANGE `maxDuration` `maxDuration` SMALLINT UNSIGNED NULL DEFAULT NULL;');
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
        if (Schema::hasTable('guideline')) {
            Schema::table('guideline', function (Blueprint $table) {
                DB::statement('ALTER TABLE `guideline` CHANGE `minDuration` `minDuration` INT UNSIGNED NULL DEFAULT NULL, CHANGE `maxDuration` `maxDuration` INT UNSIGNED NULL DEFAULT NULL;');
            }); 
        }
    }
}
