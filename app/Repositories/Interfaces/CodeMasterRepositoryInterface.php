<?php

namespace App\Repositories\Interfaces;

interface CodeMasterRepositoryInterface
{
    public function listCodeMaster($param);

    public function getCodeMaster($where);

    public function addCodeMaster($param);

    public function updateCodeMaster($param);

    public function deleteCodeMaster($where);

}
