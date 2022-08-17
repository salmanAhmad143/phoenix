<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ContentTableSeeder::class,
            RoleUserDefaultSeeder::class,
            PermissionRoleUserDefaultSeeder::class,
            UserDefaultSeeder::class,
            LanguageTableSeeder::class,
            GuidelineDefaultSeeder::class,
            WorkflowSeeder::class,
            WorkflowStateSeeder::class,
            WorkflowTransitionSeeder::class,
            WorkflowProcessSeeder::class,
        ]);
    }
}
