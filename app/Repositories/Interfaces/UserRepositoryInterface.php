<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function login($where);

    public function logout();

    public function paginateUser($param);

    public function listUser($param);

    public function getUser($where);

    public function insertData($indicator);

    public function bulkInsertData($indicator);

    public function updateUser($param);

    public function deleteUser($where);
}
