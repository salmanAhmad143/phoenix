<?php

namespace Modules\Shortcut\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class DefaultShortcutTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //Delete all shortcuts.
        DB::table('shortcuts')->delete();
        //Delete all user created shortcuts.
        DB::table('user_shortcuts')->delete();
        //Insert shortcuts.
        DB::table('shortcuts')->insert([
            ['shortcut' => 'ctrl+f', 'description' => 'Full screen video', 'operation' => 'fullscreen'],
            ['shortcut' => 'ctrl+alt+p', 'description' => 'Pause video', 'operation' => 'pauseVideo'],
            ['shortcut' => 'ctrl+p', 'description' => 'Toggle play/pause video', 'operation' => 'togglePlayPause'],
            ['shortcut' => 'alt+z', 'description' => 'Move 100ms back', 'operation' => 'backTimeOffset'],
            ['shortcut' => 'alt+x', 'description' => 'Move 100ms forward', 'operation' => 'forwardTimeOffset'],
            ['shortcut' => 'alt+left', 'description' => 'Go 0.5 second back in video', 'operation' => 'backVideoFiveSec'],
            ['shortcut' => 'alt+right', 'description' => 'Go 0.5 second forward in video', 'operation' => 'forwardVideoFiveSec'],
            ['shortcut' => 'ctrl+left', 'description' => 'Go 0.1 second back in video', 'operation' => 'backVideoOneSec'],
            ['shortcut' => 'ctrl+right', 'description' => 'Go 0.1 second forward in video', 'operation' => 'forwardVideoOneSec'],
            ['shortcut' => 'alt+up', 'description' => 'Go one line up in subtitle list view', 'operation' => 'moveLineUp'],
            ['shortcut' => 'alt+down', 'description' => 'Go one line down in subtitle list view', 'operation' => 'moveLineDown'],
            ['shortcut' => 'ctrl+shift+insert', 'description' => 'Insert subtitle before first selected line', 'operation' => 'insertSubtitleBefore'],
            ['shortcut' => 'alt+insert', 'description' => 'Insert subtitle after first selected line', 'operation' => 'insertSubtitleAfter'],
            ['shortcut' => 'ctrl+shift+a', 'description' => 'Select all subtitles', 'operation' => 'selectAllSubtitles'],
            ['shortcut' => 'ctrl+d', 'description' => 'Select only first selected line', 'operation' => 'selectFirstSubtitle'],
            ['shortcut' => 'ctrl+delete', 'description' => 'Delete selected lines', 'operation' => 'deleteSubtitle'],
            ['shortcut' => 'ctrl+shift+m', 'description' => 'Merge selected lines', 'operation' => 'mergeSubtitle'],
            ['shortcut' => 'ctrl+g', 'description' => 'Go to Subtitle Line', 'operation' => 'goToSubtitle'],
            ['shortcut' => 'ctrl+b', 'description' => 'Break Line within the subtitle', 'operation' => 'breakSubtitle'],
            ['shortcut' => 'ctrl+shift+f', 'description' => 'Fast forward video', 'operation' => 'fastForward'],
            ['shortcut' => 'ctrl+u', 'description' => 'Make selection Upper case', 'operation' => 'makeSelectionUpper'],
            ['shortcut' => 'ctrl+alt+u', 'description' => 'Make selection Lower case', 'operation' => 'makeSelectionLower'],
            ['shortcut' => 'ctrl+shift+c', 'description' => 'Change case of words starting after a fullstop.', 'operation' => 'changeCaseFullStop'],
            ['shortcut' => 'ctrl+h', 'description' => 'Find & Replace in subtitle lines', 'operation' => 'findReplaceSubtitle'],
            ['shortcut' => 'ctrl+alt+v', 'description' => 'Split line at cursor position', 'operation' => 'splitCursorPoint'],
            ['shortcut' => 'ctrl+shift+r', 'description' => 'Toggle right to left typing language', 'operation' => 'typingDirection'],
            ['shortcut' => 'alt+n', 'description' => 'To create new subtitle line', 'operation' => 'insertSubtitle'],
            ['shortcut' => 'ctrl+shift+h', 'description' => 'To remove SDH at once', 'operation' => 'removeSdh'],
            ['shortcut' => 'ctrl+space', 'description' => 'Set start and offset the rest', 'operation' => 'setOffsetRest'],
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
