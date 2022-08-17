<?php

namespace App\Repositories;

use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\ClientOperationalPoc;
use App\Repositories\Interfaces\ClientOperationalPocRepositoryInterface;
use App\Helpers\common;

class ClientOperationalPocRepository implements ClientOperationalPocRepositoryInterface
{

    public function addOperationalPoc($param)
    {
        if (!empty($param['operationalPoc'])) {
            $common = new common();
            foreach($param['operationalPoc'] as $operationalPoc) {
                if (!empty($operationalPoc)) {
                    $idParam['id'] = $operationalPoc;
                    $idParam['errorMeg'] = "constant.OPERATIONAL_POC_ID_INCORRECT_MESSAGE";
                    $indicator['userId'] = $common->getDecodeId($idParam);
                }
                $indicator['clientId'] = $param['clientId'];
                $indicator['createdBy'] = auth()->user()->userId;
                $indicator['createdAt'] = Carbon::now()->toDateTimeString();
                $indicators[] = $indicator;
            }
            ClientOperationalPoc::insert($indicators);
        }
    }

    public function deleteOperationalPoc($where)
    {
        ClientOperationalPoc::where($where)->delete();
    }
}
