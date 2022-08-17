<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserShortcutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shortcuts', function (Blueprint $table) {
            $table->increments('userShortcutId');
            $table->unsignedInteger('userId');
            $table->unsignedInteger('shortcutId');
            $table->string('customShortcut', 20)->unique();
            $table->unsignedInteger('createdBy');
            $table->dateTime('createdAt');

            $table->foreign('userId')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('shortcutId')->references('shortcutId')->on('shortcuts')->onUpdate('cascade');
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
        Schema::dropIfExists('user_shortcuts');
    }
}
