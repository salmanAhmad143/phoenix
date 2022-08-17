<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InsertDataToWorkflowTransitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_transition_', function (Blueprint $table) {
            
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow_transition')->whereIn('transitionId', [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24])->delete();
        DB::table('workflow_transition')->where('transitionId', 1)->update([
            "name" => "Assign Transcription"
        ]);
        DB::table('workflow_transition')->where('transitionId', 2)->update([
            "name" => "Re-assign Transcription"
        ]);
        DB::table('workflow_transition')->insert([

            ['transitionId' => 7, 'name' => 'Assign Transcription', 'workflowId' => 2, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 8, 'name' => 'Re-assign Transcription', 'workflowId' => 2, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['transitionId' => 9, 'name' => 'Assign Transcription', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 10, 'name' => 'Re-assign Transcription', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 11, 'name' => 'Assign Editing', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 12, 'name' => 'Re-assign Editing', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            

            ['transitionId' => 13, 'name' => 'Assign Translation', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 14, 'name' => 'Re-assign Translation', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 15, 'name' => 'Assign Editing', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 16, 'name' => 'Re-assign Editing', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 17, 'name' => 'Assign Proofreading', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 18, 'name' => 'Re-assign Proofreading', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['transitionId' => 19, 'name' => 'Assign Translation', 'workflowId' => 4, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 20, 'name' => 'Re-assign Translation', 'workflowId' => 4, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['transitionId' => 21, 'name' => 'Assign Translation', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 22, 'name' => 'Re-assign Translation', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 23, 'name' => 'Assign Editing', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 24, 'name' => 'Re-assign Editing', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflow_transition_', function (Blueprint $table) {
            DB::table('workflow_transition')->whereIn('transitionId', [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24])->delete();
            DB::table('workflow_transition')->where('transitionId', 1)->update([
                "name" => "Assign Transcription/Translation"
            ]);
            DB::table('workflow_transition')->where('transitionId', 2)->update([
                "name" => "Re-assign Transcription/Translation"
            ]);
        });
    }
}
