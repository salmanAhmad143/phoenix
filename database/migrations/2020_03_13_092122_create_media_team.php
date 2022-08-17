<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_team', function (Blueprint $table) {
            $table->unsignedInteger('mediaId');
            $table->unsignedInteger('teamId');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->unique(['mediaId', 'teamId']);

            $table->foreign('mediaId')->references('mediaId')->on('media')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('teamId')->references('teamId')->on('team')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_team');
    }
}
