<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserRepository implements UserRepositoryInterface
{
    public function login($where)
    {
        $user = $this::getUser(["email" => $where['email'], "status" => 1]);
        if ($user === null) {
            return [
                "success" => false,
                "message" => "Email not activated",
            ];
        }
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return [
                "success" => false,
                "message" => "Incorrect credentials",
            ];
        }
        return [
            "success" => true,
            "data" => ['user' => $user],
        ];
    }

    public function logout()
    {
        $user = Auth::user();
        $user->api_token = null;
        $user->save();
    }

    public function paginateUser($param)
    {
        $user = User::query();
        $user->with('reportingManager:userId,name,email');
        $user->with('role:roleId,name as roleName');
        $user->with('accessLevel:id,codeValue as levelName');
        $user->with('department:id,codeValue as departmentName');
        $user->with(['additionalReporting' => function($additionalReporting) {
            $additionalReporting->select(['userId', 'reportingManager']);
        }]);
        if (isset($param['where']) && count($param['where']) > 0) {
            $user->where($param['where']);
        }
        return $user->paginate($param['size']);
    }

    public function listUser($param)
    {
        $user = User::query();
        $user->with('reportingManager:userId,name,email');
        $user->with('role:roleId,name as roleName');
        $user->with('accessLevel:id,codeValue as levelName');
        $user->with('department:id,codeValue as departmentName');
        return $user->where($param['where'])->get();
    }

    public function getUser($where)
    {
        return User::where($where)->first();
    }

    public function insertData($indicator)
    {
    }

    public function bulkInsertData($indicator)
    {
    }

    public function updateUser($param)
    {
        $user = User::where($param['where'])->first();
        $user->fill($param['indicator']);
        return $user->save();
    }

    public function deleteUser($where)
    {
        $user = User::where($where);
        if ($user !== null) {
            $user->delete();
        }
    }
}
