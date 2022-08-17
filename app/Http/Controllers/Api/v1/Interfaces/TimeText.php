<?php

namespace App\Http\Controllers\Api\v1\Interfaces;

interface TimeText
{
    public function import($param);

    public function export($param);
}
