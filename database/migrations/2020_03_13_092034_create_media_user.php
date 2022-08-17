<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_user', function (Blueprint $table) {
            $table->unsignedInteger('mediaId');
            $table->unsignedInteger('userId');
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->unique(['mediaId', 'userId']);

            $table->foreign('mediaId')->references('mediaId')->on('media')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('media_user');
    }
}
