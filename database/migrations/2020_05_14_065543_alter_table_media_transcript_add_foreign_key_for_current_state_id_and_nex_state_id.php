<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableMediaTranscriptAddForeignKeyForCurrentStateIdAndNexStateId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->dropColumn('transitionStateId');
            $table->tinyInteger('workflowStateId')->after('workflowId')->nullable();
            $table->dropColumn('currencyId');
            $table->string('currency', 10)->after('unit')->nullable();
        });
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->foreign('workflowStateId')->references('stateId')->on('workflow_state');
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
            $table->dropColumn('currency');
            $table->integer('currencyId')->after('cost')->nullable();
            $table->dropForeign(['workflowStateId']);
        });
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->dropColumn('workflowStateId');
            $table->tinyInteger('transitionStateId')->after('subtitleSyncAccuracy')->nullable();
        });
    }
}
