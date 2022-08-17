<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertDataInCodeMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('code_master', function (Blueprint $table) {
            //Delete existing record before insertion.
            DB::table('code_master')->whereIn('codeType', ['department', 'accessLevel'])->delete();
            DB::table('code_master')->insert(
                array(
                    [
                    'codeType' => 'department',
                    'codeValue' => "Project Management"
                    ],
                    [
                    'codeType' => 'department',
                    'codeValue' => "Sales Management"
                    ],
                    [
                    'codeType' => 'accessLevel',
                    'codeValue' => "Full"
                    ],
                    [
                    'codeType' => 'accessLevel',
                    'codeValue' => "Hierarchy"
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
            DB::table('code_master')->whereIn('codeType', ['department', 'accessLevel'])->delete();
        });
    }
}
