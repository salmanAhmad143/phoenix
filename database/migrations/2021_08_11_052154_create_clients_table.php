<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('clientId');      
            $table->string('clientName', 100)->nullable();     
            $table->string('address', 200)->nullable(); 
            $table->string('city', 20)->nullable();
            $table->string('postalCode', 10)->nullable(); 
            $table->integer('country')->nullable();
            $table->unsignedInteger('salesRep')->nullable();
            $table->unsignedInteger('projectManager')->nullable(); 
            $table->unsignedInteger('projectLead')->nullable();
            $table->unsignedInteger('createdBy')->nullable();
            $table->dateTime('createdAt')->nullable();
            $table->unsignedInteger('updatedBy')->nullable();
            $table->dateTime('updatedAt')->nullable();

            $table->foreign('salesRep')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('projectManager')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('projectLead')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('createdBy')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
