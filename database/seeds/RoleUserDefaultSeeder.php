<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoleUserDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role')->where('roleId', '=', '1')->delete();
        DB::table('role')->insert([
            'roleId' => 1,
            'name' => 'Admin',
            'description' => 'Default role having all permissions',
            'status' => 1,
            'createdBy' => 1,
            'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
