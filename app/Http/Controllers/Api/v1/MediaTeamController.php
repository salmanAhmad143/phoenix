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
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\TeamRepositoryInterface;

class MediaTeamController extends Controller
{
    private $mediaRepository;
    private $teamRepository;

    public function __construct(MediaRepositoryInterface $mediaRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->mediaRepository = $mediaRepository;
        $this->teamRepository = $teamRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/media/team
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $mediaTeam = $this->mediaRepository->listMediaTeam(['where' => ['mediaId' => $media->mediaId]]);
            foreach ($mediaTeam as $mediaTeam) {
                $team[] = [
                    'id' => Hashids::encode($mediaTeam->team->teamId),
                    'name' => $mediaTeam->team->name,
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
                'url' => 'api/v1/media/team',
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
     * URL: api/v1/media/team/add
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'teamId' => 'required|array',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->mediaId),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
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
            $param = ['mediaId' => $media->mediaId];
            foreach ($request->teamId as $teamId) {
                $param['teamId'][] = Hashids::decode($teamId)[0];
            }
            DB::beginTransaction();
            $this->mediaRepository->addMediaTeam($param);
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
                'url' => 'api/v1/media/team/add',
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
            "message" => config('constant.MEDIA_TEAM_ADD_MESSAGE'),
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
     * URL: api/v1/media/team/remove
     */
    public function destroy(Request $request)
    {
        try {
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $mediaTeam = $this->mediaRepository->getMediaTeam([
                'mediaId' => Hashids::decode($request->input("mediaId")),
                'teamId' => Hashids::decode($request->input("teamId"))
            ]);
            if ($mediaTeam == null) {
                throw new CustomException(config('constant.MEDIA_TEAM_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->mediaRepository->removeMediaTeam(['mediaId' => $media->mediaId, 'teamId' => $mediaTeam->teamId]);
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
                'url' => 'api/v1/media/user/remove',
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
            "message" => config('constant.MEDIA_TEAM_REMOVE_MESSAGE'),
        ]);
    }
}
