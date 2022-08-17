<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workflow')->where('workflowId', 1)->delete();
        DB::table('workflow')->insert([
            'workflowId' => 1,
            'name' => 'Default',
            'createdBy' => 1,
            'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/
    }
}
