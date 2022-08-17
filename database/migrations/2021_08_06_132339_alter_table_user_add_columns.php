<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('departmentId')->nullable()->after('roleId');
            $table->unsignedInteger('reportingManagerId')->nullable()->after('roleId');
            $table->unsignedInteger('accessLevelId')->nullable()->after('roleId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('departmentId');
            $table->dropColumn('reportingManagerId');
            $table->dropColumn('accessLevelId');
        });
    }
}
