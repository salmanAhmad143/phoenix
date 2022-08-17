<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertWorkTypeToCodeMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('code_master', function (Blueprint $table) {
            DB::table('code_master')->insert(
                array(
                    [
                    'codeType' => 'workFlowType',
                    'codeValue' => "Transcription"
                    ],
                    [
                    'codeType' => 'workFlowType',
                    'codeValue' => "Translation"
                    ]
                )
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('code_master', function (Blueprint $table) {
            DB::table('code_master')->where(['codeType' => 'workFlowType'])->delete();
        });
    }
}
