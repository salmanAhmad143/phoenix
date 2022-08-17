<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language', function (Blueprint $table) {
            $table->increments('languageId');
            $table->string('language', 50);
            $table->string('languageCode', 20);
            $table->string('languageStandard', 20);
            $table->string('region', 50)->nullable();
            $table->string('regionCode', 20)->nullable();
            $table->string('regionStandard', 20)->nullable();
            $table->boolean('autoTranslate')->default(0);
            $table->boolean('autoTranscribe')->default(0);
            $table->string('sentenceBreaker', 2)->nullable();
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language');
    }
}
