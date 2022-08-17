<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('content')->truncate();
        DB::table('content')->insert([
            ['name' => 'Member', 'code' => 'member', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Role', 'code' => 'role', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Project', 'code' => 'project', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Media', 'code' => 'media', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Team', 'code' => 'team', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Project-User', 'code' => 'project_user', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Project-Team', 'code' => 'project_team', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Media-User', 'code' => 'media_user', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Media-Team', 'code' => 'media_team', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Transcription', 'code' => 'transcription', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Translation', 'code' => 'translation', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Caption', 'code' => 'caption', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Guideline', 'code' => 'guideline', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Assignment', 'code' => 'assignment', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Mark Complete', 'code' => 'mark_complete', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['name' => 'Client', 'code' => 'client', 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
