<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProfileColumnToNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->string('secondaryEmail', 225)->nullable()->change();
            $table->integer('age')->nullable()->change();
            $table->string('gender', 15)->nullable()->change();
            $table->string('primaryMobileNo', 15)->nullable()->change();
            $table->unsignedInteger('countryId')->nullable()->change();
            $table->unsignedInteger('stateId')->nullable()->change();
            $table->unsignedInteger('cityId')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->integer('pincode')->nullable()->change();
            $table->unsignedInteger('createdBy')->nullable()->change();
        });
        Schema::table('user_login', function (Blueprint $table) {
            $table->unsignedInteger('createdBy')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->string('secondaryEmail', 225)->nullable(false)->change();
            $table->integer('age')->nullable(false)->change();
            $table->string('gender', 15)->nullable(false)->change();
            $table->string('primaryMobileNo', 15)->nullable(false)->change();
            $table->unsignedInteger('countryId')->nullable(false)->change();
            $table->unsignedInteger('stateId')->nullable(false)->change();
            $table->unsignedInteger('cityId')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
            $table->integer('pincode')->nullable(false)->change();
            $table->unsignedInteger('createdBy')->nullable(false)->change();
        });
        Schema::table('user_login', function (Blueprint $table) {
            $table->unsignedInteger('createdBy')->nullable(false)->change();
        });
    }
}
