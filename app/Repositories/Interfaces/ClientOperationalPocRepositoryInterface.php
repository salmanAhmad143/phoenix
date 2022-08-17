<?php

namespace App\Repositories\Interfaces;

interface ClientOperationalPocRepositoryInterface
{

    public function addOperationalPoc($param);

    public function deleteOperationalPoc($where);
}
