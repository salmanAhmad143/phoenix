<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\ContentRepositoryInterface;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    private $contentRepository;

    public function __construct(ContentRepositoryInterface $contentRepository)
    {
        $this->contentRepository = $contentRepository;
        $this->user = User::first();
    }


    /**
     * URL: api/v1/content
     */
    public function index(Request $request)
    {
        try {
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }

            $param = ["size" => $size];
            $where = [];
            if ($request->input('name') && !in_array($request->input('name'), ['{name}', 'undefined'])) {
                $where[] = ['name', 'like', '%' . $request->input('name') . '%'];
            }
            if (count($where) > 0) {
                $param['where'] = $where;
            }
            $contents = $this->contentRepository->paginateContent($param);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/content',
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
            "data" => ['contents' => $contents],
        ]);
    }
}
