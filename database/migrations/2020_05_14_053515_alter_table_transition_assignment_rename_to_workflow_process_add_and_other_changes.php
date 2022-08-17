<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTransitionAssignmentRenameToWorkflowProcessAddAndOtherChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transition_assignment', function (Blueprint $table) {
            $table->dropForeign(['currentStateId']);
            $table->dropForeign(['nextStateId']);
        });
        Schema::rename('transition_assignment', 'workflow_process');
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->renameColumn('transitionAssignmentId', 'processId');
        });
        Schema::table('workflow_process', function (Blueprint $table) {
            $table->unsignedTinyInteger('workflowId')->after('processId');
            $table->foreign('workflowId')->references('workflowId')->on('workflow');
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
            $table->dropForeign(['workflowId']);
            $table->dropColumn('workflowId');
            $table->renameColumn('processId', 'transitionAssignmentId');
        });
        Schema::rename('workflow_process', 'transition_assignment');
        Schema::table('transition_assignment', function (Blueprint $table) {
            $table->foreign('currentStateId')->references('transitionStateId')->on('transition_state');
            $table->foreign('nextStateId')->references('transitionStateId')->on('transition_state');
        });
    }
}
