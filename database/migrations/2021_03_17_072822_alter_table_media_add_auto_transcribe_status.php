<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableMediaAddAutoTranscribeStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->unsignedTinyInteger('autoTranscribeStatus')->nullable()
                ->comment('0: Error while generating auto transcription, 1: Auto transcription in progress, 2: Auto transcription completed ')
                ->after('auto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->dropColumn('autoTranscribeStatus');
        });
    }
}
