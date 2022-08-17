<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkflowStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow_state')->whereIn('stateId', [1, 2, 3, 4])->delete();
        DB::table('workflow_state')->insert([
            ['stateId' => 1, 'name' => 'New', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['stateId' => 2, 'name' => 'Transcription', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['stateId' => 3, 'name' => 'Editing', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['stateId' => 4, 'name' => 'Proofreading', 'workflowId' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');;
    }
}
