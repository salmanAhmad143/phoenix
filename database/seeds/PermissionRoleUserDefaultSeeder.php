<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PermissionRoleUserDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permission')->whereIn('permissionId', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15])->delete();
        DB::table('permission')->insert([
            ['permissionId' => 1, 'roleId' => 1, 'contentId' => 1, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 2, 'roleId' => 1, 'contentId' => 2, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 3, 'roleId' => 1, 'contentId' => 3, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 4, 'roleId' => 1, 'contentId' => 4, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 5, 'roleId' => 1, 'contentId' => 5, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 6, 'roleId' => 1, 'contentId' => 6, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 7, 'roleId' => 1, 'contentId' => 7, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 8, 'roleId' => 1, 'contentId' => 8, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 9, 'roleId' => 1, 'contentId' => 9, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 10, 'roleId' => 1, 'contentId' => 10, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 11, 'roleId' => 1, 'contentId' => 11, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 12, 'roleId' => 1, 'contentId' => 12, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 13, 'roleId' => 1, 'contentId' => 13, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 14, 'roleId' => 1, 'contentId' => 14, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 15, 'roleId' => 1, 'contentId' => 15, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
            ['permissionId' => 16, 'roleId' => 1, 'contentId' => 16, 'canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1, 'status' => 1, 'createdBy' => 1, 'createdAt' => Carbon::now()->toDateTimeString()],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
