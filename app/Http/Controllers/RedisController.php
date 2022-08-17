<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RedisController extends Controller
{
    public function index()
    {
        // Redis::set('name', 'Taylor');
        $values = Redis::get('name');
        return response()->json(
            [
                'name' => $values
            ]
        );
    }
}
