<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesForForeignkeysAndDefaultNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string("videoPath")->nullable()->change();
            $table->string("audioPath")->nullable()->change();
            $table->unsignedInteger("languageId")->nullable()->change();
            $table->unsignedInteger("duration")->nullable()->change();
            $table->decimal('videoFrameRate', 7, 5)->nullable()->change();
            $table->unsignedInteger('videoSampleRate')->nullable()->change();
            $table->unsignedInteger('videoBitRate')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string("videoPath")->nullable(false)->change();
            $table->string("audioPath")->nullable(false)->change();
            $table->unsignedInteger("languageId")->nullable(false)->change();
            $table->unsignedInteger("duration")->nullable(false)->change();
            $table->decimal('videoFrameRate', 7, 5)->nullable(false)->change();
            $table->unsignedInteger('videoSampleRate')->nullable(false)->change();
            $table->unsignedInteger('videoBitRate')->nullable(false)->change();
        });
    }
}
