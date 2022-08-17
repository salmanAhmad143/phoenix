<?php

namespace App\Http\Controllers\Api\v1\Factories;

class TimeTextFactory
{
    public static function getObject($textType)
    {
        $timeTextClass = "App\\Http\\Controllers\\Api\\v1\\TimeTextHandler\\" . strtoupper($textType);
        return new $timeTextClass();
    }
}
