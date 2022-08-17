<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkflowProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow_process')->whereIn('processId', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13])->delete();
        DB::table('workflow_process')->insert([
            ['processId' => 1, 'workflowId' => 1, 'currentStateId' => 1, 'nextStateId' => 2, 'transitionId' => 1, 'currentStateStatus' => 'unassigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 2, 'workflowId' => 1, 'currentStateId' => 2, 'nextStateId' => 2, 'transitionId' => 2, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 3, 'workflowId' => 1, 'currentStateId' => 2, 'nextStateId' => 2, 'transitionId' => 2, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 4, 'workflowId' => 1, 'currentStateId' => 2, 'nextStateId' => 2, 'transitionId' => 2, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 5, 'workflowId' => 1, 'currentStateId' => 2, 'nextStateId' => 3, 'transitionId' => 3, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 6, 'workflowId' => 1, 'currentStateId' => 2, 'nextStateId' => 4, 'transitionId' => 5, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 7, 'workflowId' => 1, 'currentStateId' => 3, 'nextStateId' => 3, 'transitionId' => 4, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 8, 'workflowId' => 1, 'currentStateId' => 3, 'nextStateId' => 3, 'transitionId' => 4, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 9, 'workflowId' => 1, 'currentStateId' => 3, 'nextStateId' => 3, 'transitionId' => 4, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 10, 'workflowId' => 1, 'currentStateId' => 3, 'nextStateId' => 4, 'transitionId' => 5, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 11, 'workflowId' => 1, 'currentStateId' => 4, 'nextStateId' => 4, 'transitionId' => 6, 'currentStateStatus' => 'assigned', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 12, 'workflowId' => 1, 'currentStateId' => 4, 'nextStateId' => 4, 'transitionId' => 6, 'currentStateStatus' => 'inprocess', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['processId' => 13, 'workflowId' => 1, 'currentStateId' => 4, 'nextStateId' => 4, 'transitionId' => 6, 'currentStateStatus' => 'completed', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
