<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWorkflowAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflow', function (Blueprint $table) {
            $table->unsignedTinyInteger('workflowType')->after('status')->comment('from content table');
            $table->tinyInteger('order')->default('1')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project', function (Blueprint $table) {
            $table->dropColumn('workflowType');
            $table->dropColumn('order');
        });
    }
}
