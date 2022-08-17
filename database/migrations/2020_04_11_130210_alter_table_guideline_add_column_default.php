<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableGuidelineAddColumnDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->boolean('defaultStatus')->default(0)->after('languageId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guideline', function (Blueprint $table) {
            $table->dropColumn('defaultStatus');
        });
    }
}
