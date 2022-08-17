<?php

namespace App\Repositories;

use Carbon\Carbon;

use App\Models\Role;
use App\Models\Permission;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository implements RoleRepositoryInterface
{
    public function listRole($param)
    {
        $role = Role::query();
        $role->with('permissions');
        if (isset($param['where']) && count($param['where']) > 0) {
            $role->where($param['where']);
        }
        return $role->get();
    }

    public function getRole($where)
    {
        return Role::where($where)->first();
    }

    public function addRole($param)
    {
        $role = new Role();
        $role->name = $param['name'];
        $role->description = $param['description'] ?? null;
        $role->createdBy = auth()->user()->userId;
        $role->createdAt = Carbon::now()->toDateTimeString();
        $role->save();
        if (count($param['permissions']) > 0) {
            $param = [
                'roleId' => $role->roleId,
                'permissions' => $param['permissions']
            ];
            $this->addPermissions($param);
        }
    }

    public function updateRole($param)
    {
        $role = Role::where($param['where'])->first();
        $role->name = $param['name'];
        $role->description = $param['description'] ?? null;
        $role->updatedBy = auth()->user()->userId;
        $role->updatedAt = Carbon::now()->toDateTimeString();
        $role->save();
        if (isset($param['permissions']) && count($param['permissions']) > 0) {
            $this->deletePermissions(['roleId' => $role->roleId]);
            $param = [
                'roleId' => $role->roleId,
                'permissions' => $param['permissions']
            ];
            $this->addPermissions($param);
        }
    }

    public function deleteRole($where)
    {
        $role = Role::where($where);
        if ($role !== null) {
            $role->delete();
        }
    }

    public function addPermissions($param)
    {
        $permission = new Permission();
        foreach ($param['permissions'] as $permissions) {
            $indicator = [
                'roleId' => $param['roleId'],
                'contentId' => $permissions['contentId'],
            ];
            foreach (config('constant.PERMISSION_ARRAY') as $permissionArray) {
                $indicator[$permissionArray] = 0;
            }
            foreach ($permissions['actions'] as $key => $value) {
                $indicator[$key] = $value;
            }
            $indicator['createdBy'] = auth()->user()->userId;
            $indicator['createdAt'] = Carbon::now()->toDateTimeString();
            $indicators[] = $indicator;
        }
        $permission->insert($indicators);
    }

    public function deletePermissions($where)
    {
        $permission = Permission::where($where);
        if ($permission !== null) {
            $permission->delete();
        }
    }
}
