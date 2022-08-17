<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterForeignkeysAndNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $foreignKeys = $this->listTableForeignKeys('users');
            // print_r($foreignKeys);
            // die();
            if (in_array('user_login_createdby_foreign', $foreignKeys)) {
                $table->dropForeign('user_login_createdby_foreign');
            }

            if (in_array('user_login_updatedby_foreign', $foreignKeys)) {
                $table->dropForeign('user_login_updatedby_foreign');
            }

            if (in_array('users_createdby_foreign', $foreignKeys)) {
                $table->dropForeign('users_createdby_foreign');
            }

            if (in_array('users_updatedby_foreign', $foreignKeys)) {
                $table->dropForeign('users_updatedby_foreign');
            }

            $table->string('name', 50)->nullable()->change();
        });

        Schema::table('profile', function (Blueprint $table) {
            $table->string('secondaryEmail', 225)->nullable()->change();
            $table->integer('age')->nullable()->change();
            $table->string('gender', 15)->nullable()->change();
            $table->string('primaryMobileNo', 15)->nullable()->change();
            $table->string('secondaryMobileNo', 15)->nullable()->change();
            $table->unsignedInteger('countryId')->nullable()->change();
            $table->unsignedInteger('stateId')->nullable()->change();
            $table->unsignedInteger('cityId')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->integer('pincode')->nullable()->change();
        });

        Schema::table('project', function (Blueprint $table) {
            $table->dropForeign(['workflowId']);
            $table->dropColumn('workflowId');
        });

        Schema::table('project', function (Blueprint $table) {
            $table->unsignedTinyInteger('workflowId')->nullable()->after('name');
            $table->foreign('workflowId')->references('workflowId')->on('workflow')->onUpdate('cascade');

            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);
            $table->dropForeign(['projectId']);

            $table->foreign('projectId')->references('projectId')->on('project')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('role', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('content', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('permission', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('workflow', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('transition', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('transition_state', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('transition_assignment', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('guideline', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);

            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('media_transcript', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);
            $table->dropForeign(['mediaId']);

            $table->foreign('mediaId')->references('mediaId')->on('media')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });

        Schema::table('media_caption', function (Blueprint $table) {
            $table->dropForeign(['createdby']);
            $table->dropForeign(['updatedby']);
            $table->dropForeign(['mediaTranscriptId']);

            $table->foreign('mediaTranscriptId')->references('mediaTranscriptId')->on('media_transcript')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('createdBy')->references('userId')->on('users')->onUpdate('cascade');
            $table->foreign('updatedBy')->references('userId')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    public function listTableForeignKeys($table)
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
}
