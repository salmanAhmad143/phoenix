<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;
use Vinkla\Hashids\Facades\Hashids;

class WorkFlowController extends Controller
{
    private $workflowRepository;

    public function __construct(WorkflowRepositoryInterface $workflowRepository)
    {
        $this->workflowRepository = $workflowRepository;
        $this->user = User::first();
    }
    /**
     * url: api/v1/workflow
     */
    public function index(Request $request)
    {
        try {
            $param['orderBy'] = [
                'order' => 'ASC'
            ];
            if (!empty($request->workflowType) && !in_array($request->workflowType, ['{workflowType}', 'undefined'])) {
                $param['whereHas'] = $request->workflowType;
            }
            $workFlowData = $this->workflowRepository->listWorkflow($param);
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
            "data" => ['workflow' => $workFlowData],
        ]);
    }
}
