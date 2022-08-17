<?php

namespace App\Repositories;

use App\Models\CodeMaster;
use App\Repositories\Interfaces\CodeMasterRepositoryInterface;

class CodeMasterRepository implements CodeMasterRepositoryInterface
{
    public function listCodeMaster($param)
    {
        //code here
    }

    public function getCodeMaster($where)
    {
        return CodeMaster::where($where)->select('id','codeType','codeValue')->get();
    }

    public function addCodeMaster($param)
    {
        //code here
    }

    public function updateCodeMaster($param)
    {
        //code here
    }

    public function deleteCodeMaster($where)
    {
        //code here
    }
}
