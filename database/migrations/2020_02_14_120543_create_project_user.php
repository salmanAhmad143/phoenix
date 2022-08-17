<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedInteger('projectId');
            $table->unsignedInteger('userId');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->unique(['projectId', 'userId']);

            $table->foreign('projectId')->references('projectId')->on('project')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('project_user');
    }
}
