<?php

namespace App\Http\Controllers\Api\v1\Auth;

use Exception;
use App\Models\User;
use App\Models\AdditionalReporting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\v1\ApiTokenController;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\AdditionalReportingRepositoryInterface;
use App\Helpers\common;


class RegistrationController extends ApiTokenController
{
    private $additionalReportingRepository;

    public function __construct(AdditionalReportingRepositoryInterface $additionalReportingRepository)
    {
        $this->user = User::first();
        $this->additionalReportingRepository = $additionalReportingRepository;
        $this->common = new common();
    }
    /**
     * New user ragistration and generate emailVerificationToken to verify email
     *
     * URL: api/v1/users/registration
     */
    public function registration(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|email|unique:users',
                    'roleId' => 'required|string',
                    'name' => 'required|string|min:3|max:30',
                    'departmentId' => 'required|string',
                    'accessLevelId' => 'required|string',
                ],
                [
                    'email.required' => 'Please enter an email.',
                    'name.required' => 'Please enter an name.',
                    'roleId.required' => 'Please select a role.',
                    'departmentId.required' => 'Please select a department.',
                    'accessLevelId.required' => 'Please select a access level.'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $emailVerificationCode = Str::random(80);
            DB::beginTransaction();
            $user = new User();
            if (!empty($request->roleId)) {
                $idParam['id'] = $request->roleId;
                $idParam['errorMeg'] = "constant.ROLE_ID_INCORRECT_MESSAGE";
                $user->roleId = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->departmentId)) {
                $idParam['id'] = $request->departmentId;
                $idParam['errorMeg'] = "constant.DEPARTMENT_ID_INCORRECT_MESSAGE";
                $user->departmentId = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->accessLevelId)) {
                $idParam['id'] = $request->accessLevelId;
                $idParam['errorMeg'] = "constant.ACCESS_ID_INCORRECT_MESSAGE";
                $user->accessLevelId = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->reportingManagerId)) {
                $idParam['id'] = $request->reportingManagerId;
                $idParam['errorMeg'] = "constant.REPORTING_MANAGER_ID_INCORRECT_MESSAGE";
                $user->reportingManagerId = $this->common->getDecodeId($idParam);
            }
            $user->email = $request->email;
            $user->name = $request->name;
            $user->api_token = $request->api_token ?? null;
            $user->password = $emailVerificationCode;
            $user->emailVerificationCode = $emailVerificationCode;
            $user->status = 0;
            if ($user->save()) {
                if (!empty($request->additionalReportingIds)) {
                    foreach ($request->additionalReportingIds as  $reportingId) {
                        if (!empty($reportingId)) {
                            $idParam['id'] = $reportingId;
                            $idParam['errorMeg'] = "constant.ADDITIONAL_REPORTING_MANAGER_ID_INCORRECT_MESSAGE";
                            $reportingParam['reportingId'] = $this->common->getDecodeId($idParam);
                        }
                        $reportingParam['userId'] = $user->userId;
                        $this->additionalReportingRepository->addAdditionalReporting($reportingParam);
                    }
                }
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/users/registration',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Somthing went wrong",
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.USER_REGISTER_MESSAGE'),
            "data" => [
                'emailVerificationCode' => $emailVerificationCode,
            ],
        ]);
    }

    /**
     * Verify emailVerificationToken and activate user
     *
     * URL: api/v1/users/email/verification
     */
    public function emailVerification(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|email',
                    'emailVerificationCode' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $user = User::where([
                "email" => $request->email,
                "emailVerificationCode" => $request->emailVerificationCode,
            ])->first();
            if ($user === null) {
                throw new CustomException(config('constant.LINK_HAS_ALREADY_BEEN_USED'));
            }

            DB::beginTransaction();
            $user->emailVerificationCode = null;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->emailVerifiedAt = date('Y-m-d H:i:s');
            $user->status = 1;
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
                'url' => 'api/v1/users/email/verification',
                'location' => 'RegistrationController->emailVerification()',
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
            "message" => "Email verified successfully",
        ]);
    }
}
