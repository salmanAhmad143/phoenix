<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkflowTransitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow_transition')->whereIn('transitionId', [1, 2, 3, 4, 5, 6])->delete();
        DB::table('workflow_transition')->insert([
            ['transitionId' => 1, 'name' => 'Assign Transcription', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 2, 'name' => 'Re-assign Transcription', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 3, 'name' => 'Assign Editing', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 4, 'name' => 'Re-assign Editing', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 5, 'name' => 'Assign Proofreading', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['transitionId' => 6, 'name' => 'Re-assign Proofreading', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
