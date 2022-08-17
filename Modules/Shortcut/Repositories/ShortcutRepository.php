<?php

namespace Modules\Shortcut\Repositories;

use Carbon\Carbon;
use Modules\Shortcut\Repositories\Interfaces\ShortcutRepositoryInterface;
use Modules\Shortcut\Entities\Models\Shortcut;
use Modules\Shortcut\Entities\Models\UserShortcut;
use Vinkla\Hashids\Facades\Hashids;

class ShortcutRepository implements ShortcutRepositoryInterface
{    
    //This is return the list of shortcut.
    public function getShortcut($param)
    {
        $shortcuts = Shortcut::paginate($param['size']);
        if (!empty($shortcuts)) {
            foreach($shortcuts as $shortcut) {
                $customShortcut = $this->getCustomShortcut($shortcut->shortcutId);
                $shortcut->customShortcut = $customShortcut;
                $shortcut->shortcutId = Hashids::encode($shortcut->shortcutId);
            }
            return $shortcuts;
        } else {
            return [];
        }
    }

    //This is use to update the shortcut.
    public function createShortcut($params)
    {
        if (isset($params['customShortcut']) && $params['customShortcut']!="") {
            $userId = auth()->user()->userId;
            $params['userId'] = $userId;
            $params['createdBy'] = $userId;
            $params['createdAt'] = Carbon::now()->toDateTimeString();
            return UserShortcut::updateOrCreate([
                'userId' => $userId,
                'shortcutId' => $params['shortcutId']
            ], $params);
        } else {
            return [];
        }
    }

    //This is check and return custom shortcut.
    public function getCustomShortcut($shortcutId)
    {
        if ($shortcutId) {
            $userShortcut = UserShortcut::where([
                ['shortcutId', '=', $shortcutId],
                ['userId', '=', auth()->user()->userId]
            ])->first();

            return $userShortcut->customShortcut ?? null;
        } else {
            return null;
        }
    }

    //This is return the list of login user shortcut.
    public function getUserShortcut()
    {
        $shortcuts = Shortcut::get();
        $shortcutArr = array();
        if (!empty($shortcuts)) {
            foreach($shortcuts as $key=>$shortcut) {
                $customShortcut = $this->getCustomShortcut($shortcut->shortcutId);
                $userShortcut = $customShortcut ?? $shortcut->shortcut;
                $shortcutArr['list'][$userShortcut] = $shortcut->operation;
            }
            $shortcutArr['json'] = implode(",", array_keys($shortcutArr['list']));
            return $shortcutArr;
        } else {
            return [];
        }
    }
}
