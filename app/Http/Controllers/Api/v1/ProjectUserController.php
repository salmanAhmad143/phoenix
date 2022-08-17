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
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class ProjectUserController extends Controller
{
    private $projectRepository;
    private $userRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository, UserRepositoryInterface $userRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/project/user
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'projectId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project == null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            $projectUser = $this->projectRepository->listProjectUser(['where' => ['projectId' => $project->projectId]]);
            foreach ($projectUser as $projectUser) {
                $users[] = [
                    'id' => $projectUser->user->id,
                    'name' => $projectUser->user->name,
                    'email' => $projectUser->user->email,
                ];
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/project/user',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        return response()->json([
            "success" => true,
            "data" => ['users' => $users ?? []],
        ]);
    }

    /**
     * URL: api/v1/project/user/add
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'projectId' => 'required|string',
                    'userId' => 'required|array',
                ],
                [
                    'userId.required' => config('constant.PROJECT_USER_ADD_SELECT_EMAIL_FROM_DROPDOWN')
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->projectId),
                    'status' => 1
                ]
            ]);
            if ($project == null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            foreach ($request->userId as $userId) {
                $user = $this->userRepository->getUser([
                    'userId' => Hashids::decode($userId),
                    'status' => 1
                ]);
                if ($user == null) {
                    throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
                }
            }
            $param = ['projectId' => $project->projectId];
            foreach ($request->userId as $userId) {
                $param['userId'][] = Hashids::decode($userId)[0];
            }
            DB::beginTransaction();
            if(!$this->projectRepository->addProjectUser($param)) {
                throw new CustomException(config('constant.PROJECT_USER_ALREADY_EXIST_MESSAGE'));
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
                'url' => 'api/v1/project/user/add',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
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
            "message" => config('constant.PROJECT_USER_ADD_MESSAGE'),
        ]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * URL: api/v1/project/user/remove
     */
    public function destroy(Request $request)
    {
        try {
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project == null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            $projectUser = $this->projectRepository->getProjectUser([
                'projectId' => Hashids::decode($request->input("projectId")),
                'userId' => Hashids::decode($request->input("userId"))
            ]);
            if ($projectUser == null) {
                throw new CustomException(config('constant.PROJECT_USER_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->projectRepository->removeProjectUser(['projectId' => $project->projectId, 'userId' => $projectUser->userId]);
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
                'url' => 'api/v1/project/user/remove',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'msg' => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.PROJECT_USER_REMOVE_MESSAGE'),
        ]);
    }
}
