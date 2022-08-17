<?php

namespace Modules\Shortcut\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class ShortcutContentPermisssionSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //Check and Delete content if already exist.
        $content = DB::table('content')->where('code', 'shortcut')->first();
        if ($content) {
            DB::table('content')->where('contentId', $content->contentId)->delete();
            DB::table('permission')->where('contentId', $content->contentId)->delete();
        }

        $contentId = DB::table('content')->insertGetId([
            'name' => 'Shortcut', 'code' => 'shortcut', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        
        DB::table('permission')->insert([
            'roleId' => 1, 'contentId' => $contentId, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
