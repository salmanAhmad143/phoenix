<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GuidelineDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('guideline')->where('guidelineId', 1)->delete();
        DB::table('guideline')->insert([
            'guidelineId' => 1,
            'name' => 'English - Default',
            'minDuration' => 1,
            'maxDuration' => 7,
            'frameGap' => 1,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 43,
            'maxCharsPerSecond' => 25,
            'subtitleSyncAccuracy' => 500,
            'languageId' => 29,
            'defaultStatus' => 1,
            'createdBy' => 1,
            'createdAt' => Carbon::now()->toDateTimeString()
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
