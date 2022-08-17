<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUserLoginTableToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users');

        Schema::table('user_login', function (Blueprint $table) {
            $table->dropForeign(['profileId']);
        });
        Schema::rename('user_login', 'users');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profileId');
            $table->dropColumn('rememberToken');
            $table->renameColumn('userLoginId', 'userId');
        });

        Schema::table('profile', function (Blueprint $table) {
            $table->unsignedInteger('userId')->after('pincode');
            $table->foreign('userId')->references('userId')->on('users');
            $table->dropColumn('primaryEmail');
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
            $table->dropForeign(['userId']);
            $table->dropColumn('userId');
            $table->string('primaryEmail', 100)->after('profileId');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('userId', 'userLoginId');
            $table->string('rememberToken', 100);
            $table->unsignedInteger('profileId')->after('userLoginId');
            $table->foreign('profileId')->references('profileId')->on('profile');
        });

        Schema::rename('users', 'user_login');
    }
}
