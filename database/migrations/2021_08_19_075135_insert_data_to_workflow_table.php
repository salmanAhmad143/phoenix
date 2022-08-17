<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InsertDataToWorkflowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('workflow')->whereIn('workflowId', [1, 2, 3, 4, 5, 6])->delete();
            $workFlowType = DB::table('code_master')->where('codeType', 'workFlowType')->get();
            foreach ($workFlowType as $value) {
                if (!empty($value->codeValue) && $value->codeValue == 'Transcription') {
                    /*DB::table('workflow')->where('workflowId', 1)->update([
                        "name" => "Transcription, Editing and Proofreading",
                        "order" => 3,
                        "workflowType" => $value->id
                    ]);*/
                    DB::table('workflow')->insert([
                        [
                            'workflowId' => 1,
                            'name' => 'Transcription, Editing and Proofreading',
                            'workflowType' => $value->id,
                            'order' => 3,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ],
                        [
                            'workflowId' => 2,
                            'name' => 'Transcription',
                            'workflowType' => $value->id,
                            'order' => 1,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ],
                        [
                            'workflowId' => 3,
                            'name' => 'Transcription and Editing',
                            'workflowType' => $value->id,
                            'order' => 2,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ]
                    ]);


                } else if(!empty($value->codeValue) && $value->codeValue == 'Translation') {
                    DB::table('workflow')->insert([
                        [
                            'workflowId' => 4,
                            'name' => 'Translation',
                            'workflowType' => $value->id,
                            'order' => 1,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ],
                        [
                            'workflowId' => 5,
                            'name' => 'Translation and Editing',
                            'workflowType' => $value->id,
                            'order' => 2,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ],
                        [
                            'workflowId' => 6,
                            'name' => 'Translation, Editing and Proofreading',
                            'workflowType' => $value->id,
                            'order' => 3,
                            'createdBy' => 1,
                            'createdAt' => Carbon::now()->toDateTimeString()
                        ]
                    ]);

                }
            }
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
        Schema::table('workflow', function (Blueprint $table) {
            DB::table('workflow')->where('workflowId', 1)->update([
                "name" => "Default",
                "order" => 1,
                "workflowType" => 0
            ]);
            DB::table('workflow')->whereIn('workflowId', [2, 3, 4, 5, 6])->delete();
        });
    }
}
