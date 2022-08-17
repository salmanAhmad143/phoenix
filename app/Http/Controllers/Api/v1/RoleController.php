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
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleController extends Controller
{
    private $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/role
     */
    public function index()
    {
        try {
            $roles = $this->roleRepository->listRole([]);
            foreach ($roles as $role) {
                $permission = [];
                foreach ($role->permissions as $permissions) {
                    $permission[$permissions->contentId] = [
                        "contentId" => $permissions->contentId,
                        "actions" => [
                            "canView" => $permissions->canView,
                            "canAdd" => $permissions->canAdd,
                            "canEdit" => $permissions->canEdit,
                            "canDelete" => $permissions->canDelete,
                            "canDownload" => $permissions->canDownload,
                        ]
                    ];
                }
                unset($role->permissions);
                $role->permissions = $permission;
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
        return response()->json([
            "success" => true,
            "data" => ['roles' => $roles],
        ]);
    }

    /**
     * URL: api/v1/role/create
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:50',
                    'description' => 'required|string',
                    'permissions' => 'required|array'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            DB::beginTransaction();
            $role = $this->roleRepository->getRole(['name' => $request->name]);
            if ($role != null) {
                throw new CustomException(config('constant.ROLE_EXIST_MESSAGE'));
            }
            $this->roleRepository->addRole($request->all());
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
                'url' => 'api/v1/role/create',
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
            "message" => config('constant.ROLE_CREATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/role/details
     */
    public function show(Request $request)
    {
        try {
            $roleId = Hashids::decode($request->input("roleId"));
            if (count($roleId) === 0) {
                throw new CustomException(config('constant.ROLE_ID_INCORRECT_MESSAGE'));
            }
            $role = $this->roleRepository->getRole(['roleId' => $roleId]);
            if ($role == null) {
                throw new CustomException(config('constant.ROLE_NOT_EXIST_MESSAGE'));
            }
            $temp = [];
            foreach ($role->permissions as $permissions) {
                if ($permissions->canView != null) {
                    $temp[] = $permissions->content->code . ":view";
                }
                if ($permissions->canAdd != null) {
                    $temp[] = $permissions->content->code . ":add";
                }
                if ($permissions->canEdit != null) {
                    $temp[] = $permissions->content->code . ":edit";
                }
                if ($permissions->canDelete != null) {
                    $temp[] = $permissions->content->code . ":delete";
                }
                if ($permissions->canDownload != null) {
                    $temp[] = $permissions->content->code . ":download";
                }
            }
            // $data[strtolower(str_replace(" ", "_", $role->name))] = $temp;
            $data = $temp;
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/role/details',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'msg' => "Somthing went wrong",
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => $data,
        ]);
    }

    /**
     * URL: api/v1/user/roles/details
     */
    public function userRolesDetails(Request $request)
    {
        try {
            $roleId = auth()->user()->roleId;
            $role = $this->roleRepository->getRole(['roleId' => $roleId]);
            if ($role == null) {
                throw new CustomException(config('constant.ROLE_NOT_EXIST_MESSAGE'));
            }
            $temp = [];
            foreach ($role->permissions as $permissions) {
                if ($permissions->canView != null) {
                    $temp[] = $permissions->content->code . ":view";
                }
                if ($permissions->canAdd != null) {
                    $temp[] = $permissions->content->code . ":add";
                }
                if ($permissions->canEdit != null) {
                    $temp[] = $permissions->content->code . ":edit";
                }
                if ($permissions->canDelete != null) {
                    $temp[] = $permissions->content->code . ":delete";
                }
                if ($permissions->canDownload != null) {
                    $temp[] = $permissions->content->code . ":download";
                }
            }
            // $data[strtolower(str_replace(" ", "_", $role->name))] = $temp;
            $data = [
                'userId' => Hashids::encode(auth()->user()->userId),
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'permission' => $temp,
            ];
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/role/details',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'msg' => "Somthing went wrong",
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => $data,
        ]);
    }

    /**
     * URL: api/v1/role/update
     */
    public function update(Request $request)
    {
        try {
            $roleId = Hashids::decode($request->input("roleId"));
            if (count($roleId) === 0) {
                throw new CustomException(config('constant.ROLE_ID_INCORRECT_MESSAGE'));
            }
            $role = $this->roleRepository->getRole(['roleId' => $roleId]);
            if ($role == null) {
                throw new CustomException(config('constant.ROLE_NOT_EXIST_MESSAGE'));
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:50|unique:role,name,' . $role->roleId . ',roleId',
                    'description' => 'required|string',
                    'permissions' => 'required|array'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            DB::beginTransaction();
            $param = $request->all();
            $param['where'] = ['roleId' => $role->roleId];
            unset($param['roleId']);
            $this->roleRepository->updateRole($param);
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
                'url' => 'api/v1/role/update',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                'msg' => "Somthing went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.ROLE_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * api/v1/role/delete
     */
    public function destroy(Request $request)
    {
        try {
            $role = $this->roleRepository->getRole(['roleId' => Hashids::decode($request->input("roleId"))]);
            if ($role == null) {
                throw new CustomException(config('constant.ROLE_NOT_EXIST_MESSAGE'));
            } else if (count($role->user) > 0) {
                throw new CustomException(config('constant.ROLE_ASSIGNED_MESSAGE'));
            }
            DB::beginTransaction();
            $this->roleRepository->deleteRole(['roleId' => $role->roleId]);
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
                'url' => 'api/v1/role/delete',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.ROLE_DELETE_MESSAGE'),
        ]);
    }
}
