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
use App\Repositories\Interfaces\TeamRepositoryInterface;

class ProjectTeamController extends Controller
{
    private $projectRepository;
    private $teamRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->teamRepository = $teamRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/project/team
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
            $projectTeam = $this->projectRepository->listProjectTeam(['where' => ['projectId' => $project->projectId]]);
            foreach ($projectTeam as $projectTeam) {
                $team[] = [
                    'id' => $projectTeam->team->id,
                    'name' => $projectTeam->team->name,
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
                'url' => 'api/v1/project/team',
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
            "data" => ['team' => $team ?? []],
        ]);
    }

    /**
     * URL: api/v1/project/team/add
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'projectId' => 'required|string',
                    'teamId' => 'required|array',
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
            foreach ($request->teamId as $teamId) {
                $team = $this->teamRepository->getTeam([
                    'teamId' => Hashids::decode($teamId),
                    'status' => 1
                ]);
                if ($team == null) {
                    throw new CustomException(config('constant.TEAM_NOT_EXIST_MESSAGE'));
                }
            }
            $param = ['projectId' => $project->projectId];
            foreach ($request->teamId as $teamId) {
                $param['teamId'][] = Hashids::decode($teamId)[0];
            }
            DB::beginTransaction();
            $this->projectRepository->addProjectTeam($param);
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
                'url' => 'api/v1/project/team/add',
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
            "message" => config('constant.PROJECT_TEAM_ADD_MESSAGE'),
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
     * URL: api/v1/project/team/remove
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
            $projectTeam = $this->projectRepository->getProjectTeam([
                'projectId' => Hashids::decode($request->input("projectId")),
                'teamId' => Hashids::decode($request->input("teamId"))
            ]);
            if ($projectTeam == null) {
                throw new CustomException(config('constant.PROJECT_TEAM_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->projectRepository->removeProjectTeam(['projectId' => $project->projectId, 'teamId' => $projectTeam->teamId]);
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
            "message" => config('constant.PROJECT_TEAM_REMOVE_MESSAGE'),
        ]);
    }
}
