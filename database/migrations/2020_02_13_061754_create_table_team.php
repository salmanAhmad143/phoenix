<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTeam extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team', function (Blueprint $table) {
            $table->increments('teamId');
            $table->string('name', 50);
            $table->tinyInteger('status')->default('1');
            $table->unsignedInteger('createdBy')->nullable();
            $table->dateTime('createdAt')->nullable();
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team');
    }
}
