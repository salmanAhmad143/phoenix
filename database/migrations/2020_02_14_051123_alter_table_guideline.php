<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableGuideline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->string('name')->after('guidelineId');
            $table->unsignedInteger('minDuration')->nullable()->change();
            $table->unsignedInteger('maxDuration')->nullable()->change();
            $table->dropColumn('frameGap');
            $table->dropColumn('maxLinePerSubtitle');
            $table->dropColumn('maxCharsPerLine');
            $table->dropColumn('maxCharsPerSecond');
            $table->dropColumn('subtitleSyncAccuracy');
        });

        Schema::table('guideline', function (Blueprint $table) {
            $table->unsignedTinyInteger('frameGap')->nullable()->after('maxDuration');
            $table->unsignedTinyInteger('maxLinePerSubtitle')->nullable()->after('frameGap');
            $table->unsignedTinyInteger('maxCharsPerLine')->nullable()->after('maxLinePerSubtitle');
            $table->unsignedTinyInteger('maxCharsPerSecond')->nullable()->after('maxCharsPerLine');
            $table->unsignedTinyInteger('subtitleSyncAccuracy')->nullable()->after('maxCharsPerSecond');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->unsignedInteger('minDuration')->nullable(false)->change();
            $table->unsignedInteger('maxDuration')->nullable(false)->change();
            $table->dropColumn('frameGap');
            $table->dropColumn('maxLinePerSubtitle');
            $table->dropColumn('maxCharsPerLine');
            $table->dropColumn('maxCharsPerSecond');
            $table->dropColumn('subtitleSyncAccuracy');
        });

        Schema::table('guideline', function (Blueprint $table) {
            $table->unsignedTinyInteger('frameGap')->after('maxDuration');
            $table->unsignedTinyInteger('maxLinePerSubtitle')->after('frameGap');
            $table->unsignedTinyInteger('maxCharsPerLine')->after('maxLinePerSubtitle');
            $table->unsignedTinyInteger('maxCharsPerSecond')->after('maxCharsPerLine');
            $table->unsignedTinyInteger('subtitleSyncAccuracy')->after('maxCharsPerSecond');
        });
    }
}
