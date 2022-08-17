<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InsertDataToWorkflowStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow_state', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('workflow_state')->whereIn('stateId', [5,6,7,8,9,10,11,12,13,14,15,16,17,18])->delete();
            DB::table('workflow_state')->where('stateId', 2)->update([
                "name" => "Transcription"
            ]);
            DB::table('workflow_state')->insert([

                ['stateId' => 5, 'name' => 'New', 'workflowId' => 2, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 6, 'name' => 'Transcription', 'workflowId' => 2, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 7, 'name' => 'New', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 8, 'name' => 'Transcription', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 9, 'name' => 'Editing', 'workflowId' => 3, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 10, 'name' => 'New', 'workflowId' => 4, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 11, 'name' => 'Translation', 'workflowId' => 4, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 12, 'name' => 'New', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 13, 'name' => 'Translation', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 14, 'name' => 'Editing', 'workflowId' => 5, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 15, 'name' => 'New', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 16, 'name' => 'Translation', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 17, 'name' => 'Editing', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
                ['stateId' => 18, 'name' => 'Proofreading', 'workflowId' => 6, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
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
        Schema::table('workflow_state', function (Blueprint $table) {
            DB::table('workflow_state')->whereIn('stateId', [5,6,7,8,9,10,11,12,13,14,15,16,17,18])->delete();
            DB::table('workflow_state')->where('stateId', 2)->update([
                "name" => "Transcription/Translation"
            ]);
        });
    }
}
