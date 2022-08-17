<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\AdditionalReportingRepositoryInterface;
use App\Repositories\Interfaces\CodeMasterRepositoryInterface;
use App\Helpers\common;

class UserController extends Controller
{
    private $userRepository;
    private $additionalReportingRepository;
    private $CodeMasterRepository;

    public function __construct(UserRepositoryInterface $userRepository, AdditionalReportingRepositoryInterface $additionalReportingRepository,CodeMasterRepositoryInterface $CodeMasterRepository)
    {
        $this->userRepository = $userRepository;
        $this->CodeMasterRepository = $CodeMasterRepository;
        $this->additionalReportingRepository = $additionalReportingRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/users
     */
    public function index(Request $request)
    {
        try {
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }
            $param = ["size" => $size];
            $where = [];
            //$where[] = ['userId', '!=', auth()->user()->userId];
            if ($request->input('name') && !in_array($request->input('name'), ['{name}', 'undefined'])) {
                $where[] = ['name', 'like', '%' . $request->input('name') . '%'];
            }
            if ($request->input('email') && !in_array($request->input('email'), ['{email}', 'undefined'])) {
                $where[] = ['email', 'like', '%' . $request->input('email') . '%'];
            }
            if ($request->input('userId') && !in_array($request->input('userId'), ['{userId}', 'undefined'])) {
                $where['userId'] = $request->input('userId');
            }

            if ($request->input('status') && !in_array($request->input('status'), ['{status}', 'undefined'])) {
                $where['status'] = $request->input('status');
            }

            if ($request->input('departmentName') && !in_array($request->input('departmentName'), ['{departmentName}', 'undefined'])) {
                $codeMasterWhere[] = ['codeType', '=', 'department'];
                $codeMasterWhere[] = ['codeValue', 'like', '%' . $request->input('departmentName') . '%'];
                $department = $this->CodeMasterRepository->getCodeMaster($codeMasterWhere);
                if (!empty($department[0]->id)) {
                    $where['departmentId'] = $department[0]->id;
                }
                unset($codeMasterWhere);
            }
            
            if (count($where) > 0) {
                $param['where'] = $where;
            }

            $users = $this->userRepository->paginateUser($param);
            foreach ($users as $user) {
                $tempAdditionalReportingIds = [];
                if (!empty($user->additionalReporting))  {
                    foreach ($user->additionalReporting as $reporting) {
                        $tempAdditionalReportingIds[] = $reporting->reportingUser->id;
                    }
                }
                $user->additionalReportingIds = $tempAdditionalReportingIds;
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/users',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        }
        return response()->json([
            "success" => true,
            "data" => ['users' => $users],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * URL: api/v1/user/update
     */
    public function update(Request $request)
    {
        try {
            $commom = new common();
            if ($request->userId) {
                $idParam['id'] = $request->userId;
                $idParam['errorMeg'] = "constant.USER_ID_INCORRECT_MESSAGE";
                $userId = $commom->getDecodeId($idParam);
            }
            $user = $this->userRepository->getUser(['userId' => $userId]);
            if ($user == null) {
                throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|string|unique:users,email,' . $user->userId .',userId',
                    'roleId' => 'sometimes|required|string',
                    'name' => 'required|string|min:3|max:30',
                    'departmentId' => 'required|string',
                    'accessLevelId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $indicator = $request->all();
            unset($indicator['userId']);
            unset($indicator['additionalReportingIds']);
            if ($request->roleId) {
                if (count(Hashids::decode($request->roleId)) === 0) {
                    throw new CustomException(config('constant.ROLE_ID_INCORRECT_MESSAGE'));
                }
                $indicator['roleId'] = Hashids::decode($request->roleId)[0];
            }


            if ($request->departmentId) {
                if (count(Hashids::decode($request->departmentId)) === 0) {
                    throw new CustomException(config('constant.DEPARTMENT_ID_INCORRECT_MESSAGE'));
                }
                $indicator['departmentId'] = Hashids::decode($request->departmentId)[0];
            }


            if ($request->reportingManagerId) {
                if (count(Hashids::decode($request->reportingManagerId)) === 0) {
                    throw new CustomException(config('constant.REPORTING_MANAGER_ID_INCORRECT_MESSAGE'));
                }
                $indicator['reportingManagerId'] = Hashids::decode($request->reportingManagerId)[0];
            }

            if ($request->accessLevelId) {
                if (count(Hashids::decode($request->accessLevelId)) === 0) {
                    throw new CustomException(config('constant.ACCESS_LEVEL_ID_INCORRECT_MESSAGE'));
                }
                $indicator['accessLevelId'] = Hashids::decode($request->accessLevelId)[0];
            }

            DB::beginTransaction();
            $param = ['indicator' => $indicator];
            $param['where'] = ['userId' => $user->userId];

            $this->additionalReportingRepository->deleteMultipleAdditionalReporting(['userId' => $user->userId ]);
            if ($this->userRepository->updateUser($param) && !empty($request->additionalReportingIds)) { // code for update additional reporting
                    foreach ($request->additionalReportingIds as  $reportingId) {
                        $decodeId = Hashids::decode($reportingId);
                        if (count($decodeId) === 0) {
                            throw new CustomException(config('constant.ADDITIONAL_REPORTING_MANAGER_ID_INCORRECT_MESSAGE'));
                        }
                        $reportingParam['reportingId'] = $decodeId[0];
                        $reportingParam['userId'] = $user->userId;
                        $this->additionalReportingRepository->addAdditionalReporting($reportingParam);
                    }
                    
            }

        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/user/update',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'message' => "Somthing went wrong ".$e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.USER_UPDATE_MESSAGE'),
        ]);
    }


    /**
     * URL: api/v1/user/update/status
     */
    public function updateUserStatus(Request $request)
    {
        try {
            $commom = new common();
            if ($request->userId) {
                $idParam['id'] = $request->userId;
                $idParam['errorMeg'] = "constant.USER_ID_INCORRECT_MESSAGE";
                $userId = $commom->getDecodeId($idParam);
            }
            $user = $this->userRepository->getUser(['userId' => $userId]);
            if ($user == null) {
                throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
            }
            $indicator = $request->all();
            DB::beginTransaction();
            $param = ['indicator' => $indicator];
            $param['where'] = ['userId' => $user->userId];
            $this->userRepository->updateUser($param);

        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/user/update/status',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'message' => "Somthing went wrong ".$e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.USER_UPDATE_MESSAGE'),
        ]);
    }


    /**
     * URL: api/v1/user/profile/update
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:30',
                ]
            );

            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            if (count(Hashids::decode($request->input("userId"))) === 0) {
                throw new CustomException(config('constant.USER_ID_INCORRECT_MESSAGE'));
            }
            $user = $this->userRepository->getUser(['userId' => Hashids::decode($request->input("userId"))]);
            if ($user == null) {
                throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
            }

            $indicator = $request->all();
            unset($indicator['userId']);
            
            DB::beginTransaction();
            $param = ['indicator' => $indicator];
            $param['where'] = ['userId' => $user->userId];
            $this->userRepository->updateUser($param);
        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/user/profile/update',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'message' => "Somthing went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.USER_UPDATE_MESSAGE'),
        ]);
    }


    /**
     * URL: api/v1/user/delete
     */
    public function destroy(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("userId"))) === 0) {
                throw new CustomException(config('constant.USER_ID_INCORRECT_MESSAGE'));
            }
            $user = $this->userRepository->getUser(['userId' => Hashids::decode($request->input("userId"))]);
            if ($user == null) {
                throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->userRepository->deleteUser(['userId' => $user->userId]);
        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/user/delete',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'message' => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.USER_DELETE_MESSAGE'),
        ]);
    }
}
