<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMediaTranscriptTableToAddTextFilesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->dropForeign(['transitionStateId']);
            $table->dropForeign(['workflowId']);

            $table->dropColumn('autoTranscriptionStatus');
            $table->dropColumn('frameGap');
            $table->dropColumn('maxLinePerSubtitle');
            $table->dropColumn('maxCharsPerLine');
            $table->dropColumn('maxCharsPerSecond');
            $table->dropColumn('subtitleSyncAccuracy');
            $table->dropColumn('transitionStateId');
            $table->dropColumn('workflowId');
        });

        Schema::table('media_transcript', function (Blueprint $table) {
            $table->boolean('auto')->default(false)->after('maxDuration');
            $table->string('plainTextFile')->nullable()->after('auto');
            $table->string('textBreakBy', '30')->nullable()->after('plainTextFile');
            $table->string('timeTextFile')->nullable()->after('textBreakBy');

            $table->unsignedInteger('languageId')->nullable()->change();
            $table->unsignedInteger('minDuration')->nullable()->change();
            $table->unsignedInteger('maxDuration')->nullable()->change();
            $table->decimal('cost', 9, 2)->nullable()->change();
            $table->unsignedInteger('currencyId')->nullable()->change();
            $table->string('unit', 15)->nullable()->change();
            $table->unsignedInteger('linguistId')->nullable()->change();
            $table->string('transitionStatus', 20)->nullable()->change();

            $table->unsignedTinyInteger('frameGap')->nullable()->after('maxDuration');
            $table->unsignedTinyInteger('maxLinePerSubtitle')->nullable()->after('frameGap');
            $table->unsignedTinyInteger('maxCharsPerLine')->nullable()->after('maxLinePerSubtitle');
            $table->unsignedTinyInteger('maxCharsPerSecond')->nullable()->after('maxCharsPerLine');
            $table->unsignedTinyInteger('subtitleSyncAccuracy')->nullable()->after('maxCharsPerSecond');
            $table->unsignedTinyInteger('transitionStateId')->nullable()->after('subtitleSyncAccuracy');
            $table->unsignedTinyInteger('workflowId')->nullable()->after('unit');

            $table->foreign('transitionStateId')->references('transitionStateId')->on('transition_state')->onUpdate('cascade');
            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_transcript', function (Blueprint $table) {
            $table->renameColumn('auto', 'autoTranscriptionStatus');
            $table->dropColumn('plainTextFile');
            $table->dropColumn('textBreakBy');
            $table->dropColumn('timeTextFile');
        });
    }
}
