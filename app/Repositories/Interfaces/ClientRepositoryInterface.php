<?php

namespace App\Repositories\Interfaces;

interface ClientRepositoryInterface
{

    public function addClient($param);

    public function updateClient($param);

    public function deleteClient($where);
}
