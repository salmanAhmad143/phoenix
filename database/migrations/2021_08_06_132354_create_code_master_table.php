<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_master', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->string('codeType', 30)->nullable();
            $table->string('codeValue', 250)->nullable();
            $table->integer('codeReference')->nullable();
            $table->smallInteger('sortOrder')->unsigned()->nullable()->default(0);
            $table->timestamp('createdOn')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('createdBy')->nullable();
            $table->dateTime('updateOn')->nullable();
            $table->integer('updateBy')->nullable();
            $table->dateTime('deletedOn')->nullable();
            $table->integer('deletedBy')->nullable();
            $table->tinyInteger('status')->default('1');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('code_master');
    }
}
