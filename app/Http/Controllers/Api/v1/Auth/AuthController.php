<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Api\v1\ApiTokenController;
use App\Models\User;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class AuthController extends ApiTokenController
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->user = User::first();
    }
    /**
     * Login user (generate token)
     *
     * URL: api/v1/users/login
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                    'remember_me' => 'boolean',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $rslt = $this->userRepository->login($request->all());
            if ($rslt['success'] == false) {
                throw new CustomException($rslt['message']);
            }
            DB::beginTransaction();
            $rslt['data']['user']->id = $rslt['data']['user']->id;
            $rslt['data']['user']->userRoleId = $rslt['data']['user']->role->id;
            $rslt['data']['token'] = "Bearer " . $this::update($request)['token'];
            unset($rslt['data']['user']->role);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            DB::commit();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/users/login',
                'location' => 'AuthController->login()',
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => "User logged in successfully",
            "data" => $rslt['data'],
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * URL: api/v1/users/logout
     */
    public function logout()
    {
        try {
            DB::beginTransaction();
            $this->userRepository->logout();
        } catch (Exception $e) {
            DB::rollback();
            DB::commit();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/users/logout',
                'location' => 'AuthController->logout()',
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Somthing went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => "User logged out successfully",
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
