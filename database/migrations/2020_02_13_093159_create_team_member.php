<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_member', function (Blueprint $table) {
            $table->unsignedInteger('teamId');
            $table->unsignedInteger('userId');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->unique(['teamId', 'userId']);

            $table->foreign('teamId')->references('teamId')->on('team')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_member');
    }
}
