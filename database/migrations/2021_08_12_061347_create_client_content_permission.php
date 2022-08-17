<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateClientContentPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //Check and Delete content if already exist.
        $content = DB::table('content')->where('code', 'client')->first();
        if ($content) {
            DB::table('content')->where('contentId', $content->contentId)->delete();
            DB::table('permission')->where('contentId', $content->contentId)->delete();
        }

        $contentId = DB::table('content')->insertGetId([
            'name' => 'Client', 'code' => 'client', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        
        DB::table('permission')->insert([
            'roleId' => 1, 'contentId' => $contentId, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $content = DB::table('content')->where('code', 'client')->first();
        if ($content) {
            DB::table('content')->where('contentId', $content->contentId)->delete();
            DB::table('permission')->where('contentId', $content->contentId)->delete();
        }
    }
}
