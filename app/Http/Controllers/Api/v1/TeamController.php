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

class TeamController extends Controller
{
    private $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
        $this->user = User::first();
    }


    /**
     * URL: api/v1/team
     */
    public function index(Request $request)
    {
        try {
            if($request->has('size') && !empty($request->size)) {
                $size = $request->size;
            } else {
                $size = config('constant.MAX_PAGE_SIZE');
            }

            $param['size'] = $size;
            $teams = $this->teamRepository->listTeam($param);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/team',
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
            "data" => ['teams' => $teams],
        ]);
    }

    /**
     * URL: api/v1/team/create
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:50|unique:team',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            DB::beginTransaction();
            $this->teamRepository->addTeam($request->all());
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
                'url' => 'api/v1/team/create',
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
            "message" => config('constant.TEAM_CREATE_MESSAGE'),
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
     * URL: api/v1/team/update
     */
    public function update(Request $request)
    {
        try {
            $team = $this->teamRepository->getTeam(['teamId' => Hashids::decode($request->input("teamId"))]);
            if ($team == null) {
                throw new CustomException(config('constant.TEAM_NOT_EXIST_MESSAGE'));
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:50|unique:team,name,' . $team->teamId . ',teamId',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            DB::beginTransaction();
            $param = $request->all();
            $param['where'] = ['teamId' => $team->teamId];
            unset($param['teamId']);
            $this->teamRepository->updateTeam($param);
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
                'url' => 'api/v1/team/update',
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
            "message" => config('constant.TEAM_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/team/delete
     */
    public function destroy(Request $request)
    {
        try {
            $team = $this->teamRepository->getTeam(['teamId' => Hashids::decode($request->input("teamId"))]);
            if ($team == null) {
                throw new CustomException(config('constant.TEAM_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->teamRepository->deleteTeam(['teamId' => $team->teamId]);
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
                'url' => 'api/v1/team/delete',
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
            "message" => config('constant.TEAM_DELETE_MESSAGE'),
        ]);
    }
}
