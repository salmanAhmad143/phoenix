<?php

namespace App\Repositories;

use App\Models\AdditionalReporting;
use App\Repositories\Interfaces\AdditionalReportingRepositoryInterface;

class AdditionalReportingRepository implements AdditionalReportingRepositoryInterface
{
    public function listAdditionalReporting($param)
    {
        //code here
    }

    public function getAdditionalReporting($where)
    {
        //code here
    }

    public function addAdditionalReporting($param)
    {
        $insertData['userId'] = $param['userId'];
        $insertData['reportingManager'] = $param['reportingId'];
        AdditionalReporting::create($insertData);
    }

    public function updateAdditionalReporting($param)
    {
        //code here
    }

    public function deleteAdditionalReporting($where)
    {
        //code here
    }

    public function deleteMultipleAdditionalReporting($where) 
    {
        AdditionalReporting::where($where)->delete();
    }
}
