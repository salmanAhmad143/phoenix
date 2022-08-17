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
use App\Repositories\Interfaces\UserRepositoryInterface;

class MediaUserController extends Controller
{
    private $mediaRepository;
    private $userRepository;

    public function __construct(MediaRepositoryInterface $mediaRepository, UserRepositoryInterface $userRepository)
    {
        $this->mediaRepository = $mediaRepository;
        $this->userRepository = $userRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/media/user
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
            $mediaUser = $this->mediaRepository->listMediaUser(['where' => ['mediaId' => $media->mediaId]]);
            foreach ($mediaUser as $mediaUser) {
                $users[] = [
                    'id' => $mediaUser->user->id,
                    'name' => $mediaUser->user->name,
                    'email' => $mediaUser->user->email,
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
                'url' => 'api/v1/media/user',
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
     * URL: api/v1/media/user/add
     */
    public function store(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'userId' => 'required|array',
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
            $param = ['mediaId' => $media->mediaId];
            foreach ($request->userId as $userId) {
                if (count(Hashids::decode($userId)) === 0) {
                    throw new CustomException(config('constant.USER_ID_INCORRECT_MESSAGE'));
                }
                $user = $this->userRepository->getUser([
                    'userId' => Hashids::decode($userId),
                    'status' => 1
                ]);
                if ($user == null) {
                    throw new CustomException(config('constant.USER_NOT_EXIST_MESSAGE'));
                }
                $param['userId'][] = Hashids::decode($userId)[0];
            }
            // print_r($param);
            DB::beginTransaction();
            if(!$this->mediaRepository->addMediaUser($param)) {
                throw new CustomException(config('constant.MEDIA_USER_ALREADY_EXIST_MESSAGE'));
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
                'url' => 'api/v1/media/user/add',
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
            "message" => config('constant.MEDIA_USER_ADD_MESSAGE'),
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
     * URL: api/v1/media/user/remove
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
            $mediaUser = $this->mediaRepository->getMediaUser([
                'mediaId' => Hashids::decode($request->input("mediaId")),
                'userId' => Hashids::decode($request->input("userId"))
            ]);
            if ($mediaUser == null) {
                throw new CustomException(config('constant.MEDIA_USER_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->mediaRepository->removeMediaUser(['mediaId' => $media->mediaId, 'userId' => $mediaUser->userId]);
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
            "message" => config('constant.MEDIA_USER_REMOVE_MESSAGE'),
        ]);
    }
}
