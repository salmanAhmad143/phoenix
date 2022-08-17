<?php

namespace Modules\Shortcut\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShortcutDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Insert Shortcut content and permission.
     * @return void
     */
    public function run()
    {
        $this->call([
            ShortcutContentPermisssionSeederTableSeeder::class,
            DefaultShortcutTableSeeder::class,
        ]);
    }
}
