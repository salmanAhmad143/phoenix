<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWorkflowProcessAddForeignKeyForCurrentStateIdAndNexStateId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->dropColumn('currentStateId');
            $table->dropColumn('nextStateId');
        });
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->dropColumn('processId');
            $table->tinyInteger('currentStateId')->after('workflowId');
            $table->tinyInteger('nextStateId')->after('currentStateId');
        });
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->tinyInteger('processId', 1)->first();
            $table->foreign('currentStateId')->references('stateId')->on('workflow_state');
            $table->foreign('nextStateId')->references('stateId')->on('workflow_state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->dropForeign(['currentStateId']);
            $table->dropForeign(['nextStateId']);
        });
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->dropColumn('currentStateId');
            $table->dropColumn('nextStateId');
        });
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->tinyInteger('currentStateId')->after('workflowId');
            $table->tinyInteger('nextStateId')->after('currentStateId');
        });
    }
}
