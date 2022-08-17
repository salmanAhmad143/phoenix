<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project', function (Blueprint $table) {
            $table->unsignedInteger('projectManagerId')->nullable()->after('workflowId');
            $table->unsignedInteger('projectLeadId')->nullable()->after('workflowId');
            $table->unsignedTinyInteger('workFlowTranslationId')->after('workflowId');
            $table->text('note')->nullable()->after('workflowId');
            $table->dateTime('startDate')->nullable()->after('workflowId');
            $table->dateTime('dueDate')->nullable()->after('workflowId');
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
            $table->dropColumn('projectManagerId');
            $table->dropColumn('projectLeadId');
            $table->dropColumn('workFlowTranslationId');
            $table->dropColumn('note');
            $table->dropColumn('workFlowTranslationId');
        });
    }
}
