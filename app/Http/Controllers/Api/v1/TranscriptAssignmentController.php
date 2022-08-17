<?php

namespace App\Http\Controllers\Api\v1;

use App\Mail\MediaAssignment;
use App\Models\WorkflowState;
use App\Repositories\UserRepository;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\WorkflowProcessRepositoryInterface;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;
use App\Repositories\Interfaces\CaptionRepositoryInterface;
use App\Repositories\Interfaces\TranscriptAssignmentRepositoryInterface;
use App\Repositories\Interfaces\WorkflowTransitionRepositoryInterface;

class TranscriptAssignmentController extends Controller
{
    private $mediaRepository;
    private $transcriptCaptionRepository;
    private $transcriptAssignmentRepository;
    private $captionRepository;
    private $workflowProcessRepository;
    private $workflowTransitionRepository;

    public function __construct(MediaRepositoryInterface $mediaRepository, TranscriptCaptionRepositoryInterface $transcriptCaptionRepository, TranscriptAssignmentRepositoryInterface $transcriptAssignmentRepository, CaptionRepositoryInterface $captionRepository, WorkflowProcessRepositoryInterface $workflowProcessRepository, WorkflowTransitionRepositoryInterface $workflowTransitionRepository)
    {
        $this->transcriptAssignmentRepository = $transcriptAssignmentRepository;
        $this->mediaRepository = $mediaRepository;
        $this->transcriptCaptionRepository = $transcriptCaptionRepository;
        $this->captionRepository = $captionRepository;
        $this->workflowProcessRepository = $workflowProcessRepository;
        $this->workflowTransitionRepository = $workflowTransitionRepository;
        $this->user = User::first();
    }

    /**
     * url: api/v1/media/transcript/assignment
     */
    public function transcriptAssignment(Request $request)
    {
        try {
            $userId = Hashids::decode($request->linguistId);
            if (count($userId) === 0) {
                throw new CustomException(config('constant.LINGUIST_ID_INCORRECT_MESSAGE'));
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
            $validator = Validator::make(
                $request->all(),
                [
                    'languageId' => 'required|numeric',
                    'workflowStateId' => 'required|numeric',
                    'cost' => 'required|numeric',
                    'currency' => 'required|string',
                    'unit' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $workflowTransition = $this->workflowTransitionRepository->getWorkflowTransition([
                'where' => [
                    'transitionId' => $request->workflowTransitionId
                ],
                'select' => ['name','workflowId']
            ]);

            $mediaTranscriptList = $this->transcriptCaptionRepository->listTranscript([
                'where' => [
                    'mediaId' => $media->mediaId,
                    'workflowId' => $workflowTransition->workflowId,
                ],
                'languageId' => $request->languageId,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
                ]
            ]);

            if (empty($mediaTranscriptList[0])) {
                throw new CustomException(config('constant.TRANSCRIPT_NOT_EXIST_MESSAGE'));
            } else {
                $mediaTranscript = $mediaTranscriptList[0];
                $tempLinguistArray = [];
                foreach ($mediaTranscriptList as $value) {
                    if (!empty($value->linguistId)) {
                        $tempLinguistArray[] = $value->linguistId;
                    }
                }
            }

            if (in_array($userId[0], $tempLinguistArray)) { 
                if ($mediaTranscript->linguistId != $userId[0] || strpos($workflowTransition->name, 'Re-assign') === false) { 
                    throw new CustomException(config('constant.ASSIGNMENT_USER_INCORRECT_MESSAGE'));
                } 
            }

            if ($mediaTranscript->auto == 0 && $mediaTranscript->workflowStateId == 1) {
                $assignedMediaTrancript = $this->transcriptCaptionRepository->updateTranscript([
                    'where' => ['mediaTranscriptId' => $mediaTranscript->mediaTranscriptId],
                    'indicator' => [
                        'cost' => $request->cost,
                        'unit' => $request->unit,
                        'currency' => $request->currency,
                        'workflowStateId' => $request->workflowStateId,
                        'linguistId' => $userId[0],
                        'transitionStatus' => 'assigned',
                        'updatedBy' => auth()->user()->userId,
                    ]
                ]);
            } else {
                $assignedMediaTrancript = $this->transcriptCaptionRepository->addTranscript([
                    'indicator' => [
                        'mediaId' => $mediaTranscript->mediaId,
                        'languageId' => $mediaTranscript->languageId,
                        'minDuration' => $mediaTranscript->minDuration,
                        'maxDuration' => $mediaTranscript->maxDuration,
                        'frameGap' => $mediaTranscript->frameGap,
                        'maxLinePerSubtitle' => $mediaTranscript->maxLinePerSubtitle,
                        'maxCharsPerLine' => $mediaTranscript->maxCharsPerLine,
                        'maxCharsPerSecond' => $mediaTranscript->maxCharsPerSecond,
                        'subtitleSyncAccuracy' => $mediaTranscript->subtitleSyncAccuracy,
                        'auto' => 0,
                        'mediaCloudUrl' => $mediaTranscript->mediaCloudUrl,
                        'cost' => $request->cost,
                        'unit' => $request->unit,
                        'currency' => $request->currency,
                        'workflowId' => $mediaTranscript->workflowId,
                        'workflowStateId' => $request->workflowStateId,
                        'linguistId' => $userId[0],
                        'transitionStatus' => 'assigned',
                        'createdBy' => auth()->user()->userId,
                    ]
                ]);
                $captions = $mediaTranscript->mediaCaption;
                if ($captions != null) {
                    $captionArray = [];
                    foreach ($captions->toArray() as $caption) {
                        $caption['mediaTranscriptId'] = $assignedMediaTrancript->mediaTranscriptId;
                        unset($caption['mediaCaptionId']);
                        $caption['sourceText'] = $caption['sourceText'];
                        $caption['targetText'] = $caption['targetText'];
                        $caption['createdBy'] = auth()->user()->userId;
                        $caption['createdAt'] = Carbon::now();
                        if (isset($caption['id'])) {
                            unset($caption['id']);
                        }
                        $captionArray[] = $caption;
                    }
                    $this->captionRepository->insertCaption(['indicator' => $captionArray]);
                }
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/role',
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
        
        //Fetching details of linguist from users table with id
        $userRep = new UserRepository();

        $linguist = $userRep->getUser([
            'userId' => $userId[0],
            'status' => 1
        ]);

        $notify = array (
            'name' => $linguist->name,
            'mediaName' => $media->name,
            'workFlowState' => ''
        );

        //Getting assigned state
        $workFlowState = WorkflowState::find($request->workflowStateId);
        if (!empty($workFlowState)){
            $notify['workFlowState'] = $workFlowState->name;
        }

        Mail::to($linguist->email)->send(new MediaAssignment($notify));

        return response()->json([
            "success" => true,
            "message" => config('constant.ASSIGNMENT_USER_ASSIGNED_MESSAGE'),
        ]);
    }

    /**
     * url: api/v1/media/transcript/assignment/workflow-transition
     */
    public function getTranscriptTransition(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'workflowId' => 'required|int',
                    'workflowStateId' => 'required|int',
                    'currentStateStatus' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $workflowProcess = $this->workflowProcessRepository->listWorkflowProcess([
                'select' => ['nextStateId as workflowStateId', 'transitionId'],
                'where' => [
                    'workflowId' => $request->input('workflowId'),
                    'currentStateId' => $request->input('workflowStateId'),
                    'currentStateStatus' => $request->input('currentStateStatus'),
                ]
            ]);
            $data['workflowTransition'] = $workflowProcess;
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/role',
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
            "data" => $data,
        ]);
    }
}
