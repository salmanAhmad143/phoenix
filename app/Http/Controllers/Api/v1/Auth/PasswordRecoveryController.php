<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Api\v1\ApiTokenController;
use App\Models\User;
use App\Notifications\Exception as NotifyException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordRecoveryController extends ApiTokenController
{
    public function __construct()
    {
        $this->user = User::first();
    }
    /**
     * Forget password generate token to generate new password
     *
     * URL: api/v1/users/password/recovery-token
     */
    public function passwordRecoveryToken(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|email',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $passwordRecoveryToken = Str::random(80);

            $user = User::where([
                "email" => $request->email,
                "status" => 1,
            ])->first();
            if ($user === null) {
                throw new CustomException("Email not registered with us or activated");
            }
            DB::beginTransaction();
            $user->passwordRecoveryToken = $passwordRecoveryToken;
            $user->save();
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
                'url' => 'api/v1/users/password/recovery-token',
                'location' => 'PasswordRecoveryController->passwordRecoveryToken()',
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
            "messsage" => "Password recovery token has been generated successfully",
            "data" => [
                'passwordRecoveryToken' => $passwordRecoveryToken,
            ],
        ]);
    }

    /**
     * Forget password match token and update password
     *
     * URL: api/v1/users/password/update
     */
    public function passwordUpdate(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'passwordRecoveryToken' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $user = User::where([
                "passwordRecoveryToken" => $request->passwordRecoveryToken,
                "status" => 1,
            ])->first();
            if ($user === null) {
                throw new CustomException(config('constant.LINK_HAS_ALREADY_BEEN_USED'));
            }

            if ($request->has('password')) {
                $validator = Validator::make(
                    $request->all(),
                    [
                        'password' => 'required|string|confirmed|min:6',
                    ]
                );
    
                if ($validator->fails()) {
                    throw new CustomException($validator->errors());
                }
    
                DB::beginTransaction();
                $user->passwordRecoveryToken = null;
                $user->password = Hash::make($request->password);
                $user->save();
                $msg = "Password has been changed successfully";
            }
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
                'url' => 'api/v1/users/password/update',
                'location' => 'PasswordRecoveryController->passwordUpdate()',
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
            "messsage" => $msg ?? "",
        ]);
    }
}
