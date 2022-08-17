<?php

namespace App\Repositories\Interfaces;

interface RoleRepositoryInterface
{
    public function listRole($param);
    
    public function getRole($where);
    
    public function addRole($param);

    public function updateRole($param);

    public function deleteRole($where);
    
    public function addPermissions($param);

    public function deletePermissions($where);
}
