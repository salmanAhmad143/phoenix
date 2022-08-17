<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableThirdPartyApiCall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_api_call', function (Blueprint $table) {
            $table->renameColumn('words', 'results');
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt')->nullable();

            $table->unsignedInteger('mediaTranscriptId')->nullable()->change();
            $table->string('url')->nullable()->change();
            $table->string('provider', 20)->nullable()->change();
            $table->dateTime('requestTime')->nullable()->change();
            $table->string('clientIp', 25)->nullable()->change();
            $table->string('callingUrl')->nullable()->change();
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
            $table->renameColumn('results', 'words');
            $table->dropColumn('createdAt');
            $table->dropColumn('updatedAt');
        });
    }
}
