<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_team', function (Blueprint $table) {
            $table->unsignedInteger('projectId');
            $table->unsignedInteger('teamId');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->unique(['projectId', 'teamId']);

            $table->foreign('projectId')->references('projectId')->on('project')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('project_team');
    }
}
