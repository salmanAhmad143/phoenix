<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->where('userId', 1)->delete();
        DB::table('users')->insert([
            'userId' => 1,
            'name' => 'Admin',
            'email' => 'pankaj.yadav@lingualconsultancy.com',
            'password' => Hash::make('demo'),
            'emailVerifiedAt' => Carbon::now()->toDateTimeString(),
            'roleId' => 1,
            'status' => 1,
            'createdBy' => 1,
            'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
