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
use App\Repositories\Interfaces\TeamRepositoryInterface;

class TeamMemberController extends Controller
{
    private $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->user = User::first();
    }
    /**
     * URL: api/v1/team/member
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'teamId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $team = $this->teamRepository->getTeam(['teamId' => Hashids::decode($request->input("teamId"))]);
            if ($team == null) {
                throw new CustomException(config('constant.TEAM_MEMBER_NOT_EXIST_MESSAGE'));
            }
            $teamMember = $this->teamRepository->listTeamMember(['where' => ['teamId' => $team->teamId]]);
            foreach ($teamMember as $teamMember) {
                $users[] = [
                    'id' => $teamMember->member->id,
                    'name' => $teamMember->member->name,
                    'email' => $teamMember->member->email,
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
                'url' => 'api/v1/team/member',
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
            "data" => ['users' => $users??[]],
        ]);
    }

    /**
     * URL: api/v1/team/member/add
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'teamId' => 'required|string',
                    'userId' => 'required|array',
                ],
                [
                    'userId.required' => config('constant.TEAM_MEMBER_ADD_SELECT_EMAIL_FROM_DROPDOWN'),
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $team = $this->teamRepository->getTeam(['teamId' => Hashids::decode($request->input("teamId"))]);
            if ($team == null) {
                throw new CustomException(config('constant.TEAM_NOT_EXIST_MESSAGE'));
            }
            $param = ['teamId' => $team->teamId];
            foreach ($request->userId as $userId) {
                $param['userId'][] = Hashids::decode($userId)[0];
            }
            DB::beginTransaction();
            if(!$this->teamRepository->addTeamMember($param)) {
                throw new CustomException(config('constant.TEAM_MEMBER_ALREADY_EXIST_MESSAGE'));
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
                'url' => 'api/v1/team/member/add',
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
            "message" => config('constant.TEAM_MEMBER_ADD_MESSAGE'),
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
     * URL: api/v1/team/member/remove
     */
    public function destroy(Request $request)
    {
        try {
            $team = $this->teamRepository->getTeam(['teamId' => Hashids::decode($request->input("teamId"))]);
            if ($team == null) {
                throw new CustomException(config('constant.TEAM_NOT_EXIST_MESSAGE'));
            }
            $teamMember = $this->teamRepository->getTeamMember([
                'teamId' => Hashids::decode($request->input("teamId")),
                'userId' => Hashids::decode($request->input("userId"))
            ]);
            if ($teamMember == null) {
                throw new CustomException(config('constant.TEAM_MEMBER_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->teamRepository->removeTeamMember(['teamId' => $team->teamId, 'userId' => $teamMember->userId]);
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
                'url' => 'api/v1/team/member/remove',
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
            "message" => config('constant.TEAM_MEMBER_REMOVE_MESSAGE'),
        ]);
    }
}
