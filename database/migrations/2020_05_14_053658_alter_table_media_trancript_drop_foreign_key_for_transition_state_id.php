<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableMediaTrancriptDropForeignKeyForTransitionStateId extends Migration
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
            $table->foreign('transitionStateId')->references('transitionStateId')->on('transition_state');
        });
    }
}
