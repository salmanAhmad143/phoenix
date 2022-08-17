<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePermissionRenameActionColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->renameColumn('view', 'canView');
            $table->renameColumn('add', 'canAdd');
            $table->renameColumn('edit', 'canEdit');
            $table->renameColumn('delete', 'canDelete');
            $table->renameColumn('download', 'canDownload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->renameColumn('canView', 'view');
            $table->renameColumn('canAdd', 'add');
            $table->renameColumn('canEdit', 'edit');
            $table->renameColumn('canDelete', 'delete');
            $table->renameColumn('canDownload', 'download');
        });
    }
}
