<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignkeyOnDeleteInThirdPartyApiCallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_api_call', function (Blueprint $table) {
            $table->dropForeign(['mediaTranscriptId']);
            $table->foreign('mediaTranscriptId')->references('mediaTranscriptId')->on('media_transcript')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_api_call', function (Blueprint $table) {
            $table->dropForeign(['mediaTranscriptId']);
            $table->foreign('mediaTranscriptId')->references('mediaTranscriptId')->on('media_transcript')->onUpdate('cascade');
        });
    }
}
