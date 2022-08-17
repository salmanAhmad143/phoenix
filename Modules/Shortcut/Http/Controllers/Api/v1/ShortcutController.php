<?php

namespace Modules\Shortcut\Http\Controllers\Api\v1;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Notifications\Exception as NotifyException;
use Modules\Shortcut\Repositories\Interfaces\ShortcutRepositoryInterface;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\User;
use Exception;

class ShortcutController extends Controller
{
    private $shortcutRepository;
    private $request;

    public function __construct(ShortcutRepositoryInterface $shortcutRepository, Request $request)
    {
        $this->shortcutRepository = $shortcutRepository;
        $this->request = $request;
        $this->user = User::first();
    }
    /**
     * /api/v1/shortcuts
     * Return the list of shortcuts.
     */
    public function index()
    {
        try {
            if($this->request->has('size') && !empty($this->request->size)) {
                $size = $this->request->size;
            } else {
                $size = config('constant.MAX_PAGE_SIZE');
            }

            $param['size'] = $size;
            $shortcuts = $this->shortcutRepository->getShortcut($param);            
            return response()->json([
                "success" => true,
                "shortcut" => $shortcuts,
            ]);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/shortcuts',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong. ".$e->getMessage(),
                "errorCode" => '',
            ]);
        }
    }

    /**
     * /api/v1/shortcut/update
     * Update the selected shortcut.
     */
    public function update()
    {
        return DB::transaction(function () {
            try {
                $validator = Validator::make(
                    $this->request->all(),
                    [
                        'shortcutId' => 'required',
                        'customShortcut' => 'required|unique:shortcuts,shortcut|unique:user_shortcuts,customShortcut,null,userId'.auth()->user()->userId
                    ]
                );

                if ($validator->fails()) {
                    throw new CustomException($validator->errors());
                }

                if (count(Hashids::decode($this->request->shortcutId)) === 0) {
                    throw new CustomException(config('constant.SHORTCUT_ID_INCORRECT_MESSAGE'));
                }

                $params['shortcutId'] = Hashids::decode($this->request->shortcutId)[0];
                $params['customShortcut'] = $this->request->customShortcut;
                $this->shortcutRepository->createShortcut($params);         
                return response()->json([
                    "success" => true,
                    "message" => config('constant.SHORTCUT_UPDATE_MESSAGE')
                ]);
            } catch (CustomException $e) {
                return response()->json([
                    "success" => false,
                    "message" => $e->getMessage(),
                    "errorCode" => '',
                ]);
            } catch (Exception $e) {
                $this->user->notify(new NotifyException([
                    'url' => 'api/v1/shortcut/update',
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage(),
                ]));
                return response()->json([
                    "success" => false,
                    "message" => "Something went wrong. ".$e->getMessage(),
                    "errorCode" => '',
                ]);
            }
        });
    }

    /**
     * /api/v1/shortcut/list
     * Update the selected shortcut.
     */
    public function listAllShortcut()
    {
        try {
            $shortcuts = $this->shortcutRepository->getUserShortcut();            
            return response()->json([
                "success" => true,
                "shortcut" => $shortcuts,
            ]);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/shortcuts',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong. ".$e->getMessage(),
                "errorCode" => '',
            ]);
        }
    }
}
