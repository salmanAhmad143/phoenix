<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\User;
use Closure;
use Exception;
use App\Exceptions\CustomException;
use App\Notifications\Exception as NotifyException;

class Permissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $content, $action)
    {
        try {
            $permissions = Permission::where('roleId', auth()->user()->roleId)->whereHas('content', function ($query) use ($content) {
                $query->where('code', '=', $content);
            })->first();
            if ($permissions == null || $permissions->$action == 0) {
                throw new CustomException(config('constant.PERMISSION_DENIED'));
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            User::first()->notify(new NotifyException([
                'url' => $request->path(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
            ]);
        }
        return $next($request);
    }
}
