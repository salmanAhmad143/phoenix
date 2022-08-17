<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientOperationalPocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_operational_pocs', function (Blueprint $table) {
            $table->increments('operationalPocId');
            $table->unsignedInteger('clientId');
            $table->unsignedInteger('userId');
            $table->unsignedInteger('createdBy')->nullable();
            $table->dateTime('createdAt')->nullable();

            $table->foreign('clientId')->references('clientId')->on('clients')->onUpdate('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_operational_pocs');
    }
}
