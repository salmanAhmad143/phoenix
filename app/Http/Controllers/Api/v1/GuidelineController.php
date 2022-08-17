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
use App\Repositories\Interfaces\GuidelineRepositoryInterface;

class GuidelineController extends Controller
{
    private $guidelineRepository;

    public function __construct(GuidelineRepositoryInterface $guidelineRepository)
    {
        $this->guidelineRepository = $guidelineRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/guideline
     */
    public function index(Request $request)
    {
        try {
            $param = array();
            $where = array();
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }

            if ($request->input('name') && !in_array($request->input('name'), ['{name}', 'undefined'])) {
                $where['name'] = $request->input('name');
            }
            if ($request->input('languageId') && !in_array($request->input('languageId'), ['{languageId}', 'undefined'])) {
                $where['languageId'] = $request->input('languageId');
            }
            if (count($where) > 0) {
                $param['where'] = $where;
            }
            $param['orderBy'] = ['defaultStatus' => 'DESC'];
            $param['size'] = $size;
            $guidelines = $this->guidelineRepository->listGuideline($param);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/guideline',
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
            "data" => ['guidelines' => $guidelines],
        ]);
    }

    /**
     * URL: api/v1/guideline/create
     */
    public function store(Request $request)
    {
        try {
            //validate request
            $validator = $this->validation($request);
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            DB::beginTransaction();
            if ($request->defaultStatus == 1) {
                $this->guidelineRepository->updateGuidelines([
                    'indicator' => ['defaultStatus' => 0],
                    'where' => ['languageId' => $request->languageId]
                ]);
            }
            $indicator = $request->all();
            $indicator['createdBy'] = auth()->user()->userId;
            $this->guidelineRepository->addGuideline(['indicator' => $indicator]);
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
                'url' => 'api/v1/guideline/create',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong".$e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.GUIDELINE_CREATE_MESSAGE'),
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
     * URL: api/v1/guideline/update
     */
    public function update(Request $request)
    {
        try {
            //validate request.
            $validator = $this->validation($request);            
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            
            $guideline = $this->guidelineRepository->getGuideline(['guidelineId' => Hashids::decode($request->input("guidelineId"))]);
            if ($guideline == null) {
                throw new CustomException(config('constant.GUIDELINE_NOT_EXIST_MESSAGE'));
            }

            DB::beginTransaction();
            if ($request->defaultStatus == 1) {
                $this->guidelineRepository->updateGuidelines([
                    'indicator' => ['defaultStatus' => 0],
                    'where' => ['languageId' => $request->languageId]
                ]);
            }
            $param['indicator'] = $request->all();
            $param['indicator']['updatedBy'] = auth()->user()->userId;
            $param['where'] = ['guidelineId' => $guideline->guidelineId];
            unset($param['indicator']['guidelineId']);
            $this->guidelineRepository->updateGuideline($param);
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
                'url' => 'api/v1/guideline/update',
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
            "message" => config('constant.GUIDELINE_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/guideline/delete
     */
    public function destroy(Request $request)
    {
        try {
            $guideline = $this->guidelineRepository->getGuideline(['guidelineId' => Hashids::decode($request->input("guidelineId"))]);
            if ($guideline == null) {
                throw new CustomException(config('constant.GUIDELINE_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $this->guidelineRepository->deleteGuideline(['guidelineId' => $guideline->guidelineId]);
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
                'url' => 'api/v1/guideline/delete',
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
            "message" => config('constant.GUIDELINE_DELETE_MESSAGE'),
        ]);
    }

    //This is validate the requrest parameters of guideline.
    private function validation($request)
    {
        $guidelineId = Hashids::decode($request->guidelineId)[0] ?? null;
        return Validator::make(
            $request->all(),
            [
                'name' => 'required|unique:guideline,name,'.$guidelineId.',guidelineId,languageId,' . $request->languageId,
                'languageId' => 'required',
                'minDuration' => 'required|numeric',
                'maxDuration' => 'required|numeric|gte:minDuration',
                'frameGap' => 'required|numeric',
                'maxLinePerSubtitle' => 'required|numeric',
                'maxCharsPerLine' => 'required|numeric',
                'maxCharsPerSecond' => 'required|numeric',
            ],
            [   
                'name.required' => config('constant.GUIDELINE_NAME_REQURIED_MESSAGE'),
                'name.unique' => config('constant.GUIDELINE_NAME_URIQUE_MESSAGE'),
                'languageId.required' => config('constant.GUIDELINE_LANGUAGE_REQUIRED_MESSAGE'),
            ]
        );
    }
}
