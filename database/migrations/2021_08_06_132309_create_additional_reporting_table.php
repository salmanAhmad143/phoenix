<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalReportingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_reporting', function (Blueprint $table) {
            $table->increments('reportingId');
            $table->unsignedInteger('userId')->nullable();
            $table->unsignedInteger('reportingManager')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('createdAt');
            $table->dateTime('updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additional_reporting');
    }
}
