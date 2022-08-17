<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRenameTableTransitionStateToWorkflowState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('transition_state', 'workflow_state');
        Schema::table('workflow_state', function (Blueprint $table) {
            $table->dropColumn('transitionStateId');
        });
        Schema::table('workflow_state', function (Blueprint $table) {
            $table->tinyInteger('stateId', 1)->first();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_state', function (Blueprint $table) {
            $table->renameColumn('stateId', 'transitionStateId');
        });
        Schema::rename('workflow_state', 'transition_state');
    }
}
