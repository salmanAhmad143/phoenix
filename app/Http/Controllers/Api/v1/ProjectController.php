<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowTransition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\ProjectTagRepositoryInterface;
use App\Helpers\common;

class ProjectController extends Controller
{
    private $projectRepository;
    private $mediaRepository;
    private $tagRepository;
    private $projectTagRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository, MediaRepositoryInterface $mediaRepository, TagRepositoryInterface $tagRepository, ProjectTagRepositoryInterface $projectTagRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->mediaRepository = $mediaRepository;
        $this->tagRepository = $tagRepository;
        $this->projectTagRepository = $projectTagRepository;
        $this->user = User::first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * URL: api/v1/projects
     */
    public function index(Request $request)
    {
        try {
            //TODO: find better way to send pagination  details through API resposne.
            //TODO: API structre is not standard one.
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }
            $project = $this->projectRepository->paginateProject([
                'userId' => auth()->user()->userId,
                'size' => $size,
                'where' => [
                    'status' => 1
                ]
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/projects',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                'msg' => $e->getMessage(),
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => [
                'projects' => $project,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * URL: api/v1/projects/create
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:100|unique:project', //TODO: add project name size validation and increase size to 100 chars instaed of 50
                    'workflowId' => 'required|string',
                    'workFlowTranslationId' => 'required|string',
                    'startDate' => 'nullable|date_format:Y-m-d|date|before_or_equal:dueDate',
                    'dueDate' => 'nullable|date_format:Y-m-d|date|after_or_equal:startDate',
                    'clientId' => 'required|string',
                ],
                [
                    'name.required' => 'Please enter a project name.',
                    'workflowId.required' => 'Please select a workflow.',
                    'workFlowTranslationId.required' => 'Please select a workflow translation.',
                    'startDate' => 'Start date format is not correct.',
                    'dueDate' => 'Due date format is not correct.',
                    'clientId' => 'Please select a client.'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            DB::beginTransaction();
            $param['indicator'] = [
                'name' => $request->name,
                'createdBy' => auth()->user()->userId,
            ];
            $commom = new common();
            if ($request->workflowId) {
                $idParam['id'] = $request->workflowId;
                $idParam['errorMeg'] = "constant.WORKFLOW_ID_NOT_EXIST_MESSAGE";
                $param['indicator']['workflowId'] = $commom->getDecodeId($idParam);
            }
            if ($request->workFlowTranslationId) {
                $idParam['id'] = $request->workFlowTranslationId;
                $idParam['errorMeg'] = "constant.TRANSLATION_ID_NOT_EXIST_MESSAGE";
                $param['indicator']['workFlowTranslationId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->projectManagerId)) {
                $idParam['id'] = $request->projectManagerId;
                $idParam['errorMeg'] = "constant.PROJECT_MANAGER_NOT_EXIST_MESSAGE";
                $param['indicator']['projectManagerId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->projectLeadId)) {
                $idParam['id'] = $request->projectLeadId;
                $idParam['errorMeg'] = "constant.PROJECT_LEAD_NOT_EXIST_MESSAGE";
                $param['indicator']['projectLeadId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->clientId)) {
                $idParam['id'] = $request->clientId;
                $idParam['errorMeg'] = "constant.CLIENT_NOT_EXIST_MESSAGE";
                $param['indicator']['clientId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->startDate)) {
                $param['indicator']['startDate'] = $request->startDate;
            }


            if (!empty($request->dueDate)) {
                $param['indicator']['dueDate'] = $request->dueDate;
            }

            if (!empty($request->note)) {
                $param['indicator']['note'] = $request->note;
            }

            $projectData = $this->projectRepository->addProject($param);
            if (!empty($projectData->projectId) && !empty($request->tags)) {
                $tagParam['projectId'] = $projectData->projectId;
                $tagParam['tags'] = $request->tags;
                $this->updateTagData($tagParam);
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
                'url' => 'api/v1/projects/create',
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
            "message" => config('constant.PROJECT_CREATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/projects/details
     */
    public function show(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("projectId"))) === 0) {
                throw new CustomException(config('constant.PROJECT_ID_INCORRECT_MESSAGE'));
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project === null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/projects/details',
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
            "data" => [
                'projects' => $project,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * URL: api/v1/projects/update
     */
    public function update(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("projectId"))) === 0) {
                throw new CustomException(config('constant.PROJECT_ID_INCORRECT_MESSAGE'));
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
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:100|unique:project,name,' . $project->projectId . ',projectId',
                    'workflowId' => 'required|string',
                    'workFlowTranslationId' => 'required|string',
                    'startDate' => 'nullable|date_format:Y-m-d|date|before_or_equal:dueDate',
                    'dueDate' => 'nullable|date_format:Y-m-d|date|after_or_equal:startDate',
                    'clientId' => 'required|string'
                ],
                [
                    'name.required' => 'Please enter a project name.',
                    'workflowId.required' => 'Please select a workflow.',
                    'workFlowTranslationId.required' => 'Please select a workflow translation.',
                    'startDate' => 'Start date format is not correct.',
                    'dueDate' => 'Due date format is not correct.',
                    'clientId.required' => 'Please select a client.',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            DB::beginTransaction();

            $indicator = [
                'name' => $request->name,
                'workflowId' => null,
                'projectManagerId' => null,
                'projectLeadId' => null,
                'updatedBy' => auth()->user()->userId,
            ];
            $commom = new common();
            if ($request->input('workflowId')) {
                $idParam['id'] = $request->workflowId;
                $idParam['errorMeg'] = "constant.WORKFLOW_ID_NOT_EXIST_MESSAGE";
                $indicator['workflowId'] = $commom->getDecodeId($idParam);
            }
            if ($request->workFlowTranslationId) {
                $idParam['id'] = $request->workFlowTranslationId;
                $idParam['errorMeg'] = "constant.TRANSLATION_ID_NOT_EXIST_MESSAGE";
                $indicator['workFlowTranslationId'] = $commom->getDecodeId($idParam);
            }
            if (!empty($request->projectManagerId)) {
                $idParam['id'] = $request->projectManagerId;
                $idParam['errorMeg'] = "constant.PROJECT_MANAGER_NOT_EXIST_MESSAGE";
                $indicator['projectManagerId'] = $commom->getDecodeId($idParam);
            }
            if (!empty($request->projectLeadId)) {
                $idParam['id'] = $request->projectLeadId;
                $idParam['errorMeg'] = "constant.PROJECT_LEAD_NOT_EXIST_MESSAGE";
                $indicator['projectLeadId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->clientId)) {
                $idParam['id'] = $request->clientId;
                $idParam['errorMeg'] = "constant.CLIENT_NOT_EXIST_MESSAGE";
                $indicator['clientId'] = $commom->getDecodeId($idParam);
            }

            if (!empty($request->startDate)) {
                $indicator['startDate'] = $request->startDate;
            }

            if (!empty($request->dueDate)) {
                $indicator['dueDate'] = $request->dueDate;
            }

            if (!empty($request->note)) {
                $indicator['note'] = $request->note;
            }
            $projectData = $this->projectRepository->updateProject([
                'indicator' => $indicator,
                'where' => [
                    'projectId' => $project->projectId,
                ]
            ]);
            if (!empty ($projectData) && !empty($request->tags)) {
                $param['projectId'] = $project->projectId;
                $this->projectTagRepository->deleteProjectTag($param);
                $param['tags'] = $request->tags;
                $this->updateTagData($param);
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
                'url' => 'api/v1/projects/update',
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
            "message" => config('constant.PROJECT_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     *
     * URL: api/v1/projects/delete
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            if (count(Hashids::decode($request->input("projectId"))) === 0) {
                throw new CustomException(config('constant.PROJECT_ID_INCORRECT_MESSAGE'));
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project === null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            $this->projectRepository->deleteProject([
                'projectId' => $project->projectId,
            ]);
            $project->delete();
            Storage::deleteDirectory("projects/" . $project->projectId);
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
                'url' => 'api/v1/projects/delete',
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
            "message" => config('constant.PROJECT_DELETE_MESSAGE'),
        ]);
    }

    /**
     * Get media list of perticular project
     * URL: api/v1/projects/media
     */
    public function media(Request $request)
    {
        try {
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }
            if (count(Hashids::decode($request->input("projectId"))) === 0) {
                throw new CustomException(config('constant.PROJECT_ID_INCORRECT_MESSAGE'));
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project === null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            $projectFlag = $this->projectRepository->getProjectUser([
                'projectId' => Hashids::decode($request->input("projectId")),
                'userId' => auth()->user()->userId,
            ]);

            if ($projectFlag === null) {
                $projectFlag = $this->projectRepository->getProjectTeamUser([
                    'userId' => auth()->user()->userId,
                    'where' => [
                        'projectId' => Hashids::decode($request->input("projectId")),
                        'status' => 1
                    ],
                ]);
            }

            if ($projectFlag === null) {
                $media = $this->mediaRepository->listUserMedia([
                    'userId' => auth()->user()->userId,
                    'where' => [
                        'projectId' => Hashids::decode($request->input("projectId")),
                        'status' => 1
                    ],
                    'size' => $size,
                ]);
            } else {
                $media = $project->media()->paginate($size);
            }
            if (count($media) > 0) {
                foreach ($media as $mda) {
                    $mda->projectIdentity = $project->id;
                    $mda->workflowName = $mda->workflow->name ?? null;
                    unset($mda->workflow);
                    $language = $mda->language->language ?? null;
                    unset($mda->language);
                    $mda->language = $language;
                    $mda->videoPath = env('APP_URL') . "/api/v1/media?mediaId=" . $mda->id;
                    $mda->videoImagePath = env('APP_URL') . "/api/v1/media-image?mediaId=" . $mda->id;
                }
            }
            sleep(1);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/projects/media',
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
            "data" => [
                'media' => $media,
            ],
        ]);
    }

    //update tags in tags and project_tag table
    public function updateTagData($param) {
        if (!empty($param['tags']) && !empty($param['projectId'])) {
            foreach ($param['tags'] as $tag) {
                $tagParam['tag'] = $tag;
                $tagData = $this->tagRepository->getTag($tagParam);
                $projectTagParam['indicator']['projectId'] = $param['projectId'];
                if (!empty($tagData->tagId)) {
                    $projectTagParam['indicator']['tagId'] = $tagData->tagId;
                    $this->projectTagRepository->addProjectTag($projectTagParam);
                } else {
                    $tagInsertParam['indicator']['tag'] = $tag;
                    $tagData = $this->tagRepository->addTag($tagInsertParam);
                    if (!empty($tagData->tagId)) {
                        $projectTagParam['indicator']['tagId'] = $tagData->tagId;
                        $this->projectTagRepository->addProjectTag($projectTagParam);
                    }
                }
            }
        }
    }
}
