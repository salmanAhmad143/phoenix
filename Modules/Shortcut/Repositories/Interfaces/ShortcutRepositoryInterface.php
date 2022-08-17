<?php

namespace Modules\Shortcut\Repositories\Interfaces;

interface ShortcutRepositoryInterface
{
    public function getShortcut($param);
    public function createShortcut($params);
    public function getUserShortcut();
}
