<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InsertDataToWorkflowProcessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_process', function (Blueprint $table) {
            

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow_process')->whereIn('processId', [14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50])->delete();
        DB::table('workflow_process')->insert([

            ['processId' => 14, 'workflowId' => 2, 'currentStateId' => 5, 'nextStateId' => 6, 'transitionId' => 7, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['processId' => 15, 'workflowId' => 2, 'currentStateId' => 6, 'nextStateId' => 6, 'transitionId' => 8, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 16, 'workflowId' => 2, 'currentStateId' => 6, 'nextStateId' => 6, 'transitionId' => 8, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 17, 'workflowId' => 2, 'currentStateId' => 6, 'nextStateId' => 6, 'transitionId' => 8, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            
            ['processId' => 18, 'workflowId' => 3, 'currentStateId' => 7, 'nextStateId' => 8, 'transitionId' => 9, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 19, 'workflowId' => 3, 'currentStateId' => 8, 'nextStateId' => 8, 'transitionId' => 10, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 20, 'workflowId' => 3, 'currentStateId' => 8, 'nextStateId' => 8, 'transitionId' => 10, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 21, 'workflowId' => 3, 'currentStateId' => 8, 'nextStateId' => 8, 'transitionId' => 10, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 22, 'workflowId' => 3, 'currentStateId' => 8, 'nextStateId' => 9, 'transitionId' => 11, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 23, 'workflowId' => 3, 'currentStateId' => 9, 'nextStateId' => 9, 'transitionId' => 12, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 24, 'workflowId' => 3, 'currentStateId' => 9, 'nextStateId' => 9, 'transitionId' => 12, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 25, 'workflowId' => 3, 'currentStateId' => 9, 'nextStateId' => 9, 'transitionId' => 12, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            
            ['processId' => 26, 'workflowId' => 6, 'currentStateId' => 15, 'nextStateId' => 16, 'transitionId' => 13, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 27, 'workflowId' => 6, 'currentStateId' => 16, 'nextStateId' => 16, 'transitionId' => 14, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 28, 'workflowId' => 6, 'currentStateId' => 16, 'nextStateId' => 16, 'transitionId' => 14, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 29, 'workflowId' => 6, 'currentStateId' => 16, 'nextStateId' => 16, 'transitionId' => 14, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['processId' => 30, 'workflowId' => 6, 'currentStateId' => 16, 'nextStateId' => 17, 'transitionId' => 15, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 31, 'workflowId' => 6, 'currentStateId' => 16, 'nextStateId' => 18, 'transitionId' => 17, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 32, 'workflowId' => 6, 'currentStateId' => 17, 'nextStateId' => 17, 'transitionId' => 16, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 33, 'workflowId' => 6, 'currentStateId' => 17, 'nextStateId' => 17, 'transitionId' => 16, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 34, 'workflowId' => 6, 'currentStateId' => 17, 'nextStateId' => 17, 'transitionId' => 16, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 35, 'workflowId' => 6, 'currentStateId' => 17, 'nextStateId' => 18, 'transitionId' => 17, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 36, 'workflowId' => 6, 'currentStateId' => 18, 'nextStateId' => 18, 'transitionId' => 18, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 37, 'workflowId' => 6, 'currentStateId' => 18, 'nextStateId' => 18, 'transitionId' => 18, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 38, 'workflowId' => 6, 'currentStateId' => 18, 'nextStateId' => 18, 'transitionId' => 18, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['processId' => 39, 'workflowId' => 4, 'currentStateId' => 10, 'nextStateId' => 11, 'transitionId' => 19, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 40, 'workflowId' => 4, 'currentStateId' => 11, 'nextStateId' => 11, 'transitionId' => 20, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 41, 'workflowId' => 4, 'currentStateId' => 11, 'nextStateId' => 11, 'transitionId' => 20, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 42, 'workflowId' => 4, 'currentStateId' => 11, 'nextStateId' => 11, 'transitionId' => 20, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],

            ['processId' => 43, 'workflowId' => 5, 'currentStateId' => 12, 'nextStateId' => 13, 'transitionId' => 21, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 44, 'workflowId' => 5, 'currentStateId' => 13, 'nextStateId' => 13, 'transitionId' => 22, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 45, 'workflowId' => 5, 'currentStateId' => 13, 'nextStateId' => 13, 'transitionId' => 22, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 46, 'workflowId' => 5, 'currentStateId' => 13, 'nextStateId' => 13, 'transitionId' => 22, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 47, 'workflowId' => 5, 'currentStateId' => 13, 'nextStateId' => 14, 'transitionId' => 23, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 48, 'workflowId' => 5, 'currentStateId' => 14, 'nextStateId' => 14, 'transitionId' => 24, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 49, 'workflowId' => 5, 'currentStateId' => 14, 'nextStateId' => 14, 'transitionId' => 24, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 50, 'workflowId' => 5, 'currentStateId' => 14, 'nextStateId' => 14, 'transitionId' => 24, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
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
        Schema::table('workflow_process', function (Blueprint $table) {
            DB::table('workflow_process')->whereIn('processId', [14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50])->delete();
        });
    }
}
