<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTranscriptionColumnsInMediaTranscriptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->string('mediaCloudUrl')->nullable()->after('auto');
            $table->string('transcriptionProcessCode')->nullable()->after('mediaCloudUrl');
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
            $table->dropColumn('mediaCloudUrl');
            $table->dropColumn('transcriptionProcessCode');
        });
    }
}
