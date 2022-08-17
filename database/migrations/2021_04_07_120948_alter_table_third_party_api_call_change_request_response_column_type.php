<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableThirdPartyApiCallChangeRequestResponseColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('third_party_api_call')) {
            Schema::table('third_party_api_call', function (Blueprint $table) {
                DB::statement('ALTER TABLE `third_party_api_call` CHANGE `response` `response` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `results` `results` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('third_party_api_call')) {
            Schema::table('third_party_api_call', function (Blueprint $table) {
                DB::statement('ALTER TABLE `third_party_api_call` CHANGE `response` `response` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL, CHANGE `results` `results` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            }); 
        }
    }
}
