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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends ApiTokenController
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->user = User::first();
    }
    /**
     * Change password after login by putting old and new password
     *
     * URL: api/v1/users/password/change
     */
    public function change(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'oldPassword' => 'required|string',
                    'newPassword' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $user = User::where([
                "email" => auth()->user()->email,
                "status" => 1,
            ])->first();

            if ($user === null) {
                throw new CustomException("Email not registered with us or activated");
            }
            if (!Hash::check($request->oldPassword, $user->password)) {
                throw new CustomException("Incorrect old password");
            }
            DB::beginTransaction();
            $user->password = Hash::make($request->newPassword);
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
                'url' => 'api/v1/users/password/change',
                'location' => 'ChangePasswordController->change()',
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
            "messsage" => "Password changed successfully",
        ]);
    }
}
