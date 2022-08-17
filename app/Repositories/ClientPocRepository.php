<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\ClientPoc;
use Vinkla\Hashids\Facades\Hashids;
use App\Repositories\Interfaces\ClientPocRepositoryInterface;
use App\Helpers\common;

class ClientPocRepository implements ClientPocRepositoryInterface
{

    public function addSalesPoc($param)
    {
        if (!empty($param['salesPoc'])) {
            $common = new common();
            foreach($param['salesPoc'] as $salesPoc) {
                if (!empty($salesPoc)) {
                    $idParam['id'] = $salesPoc;
                    $idParam['errorMeg'] = "constant.SALES_POC_ID_INCORRECT_MESSAGE";
                    $indicator['userId'] = $common->getDecodeId($idParam);
                }
                $indicator['clientId'] = $param['clientId'];
                $indicator['createdBy'] = auth()->user()->userId;
                $indicator['createdAt'] = Carbon::now()->toDateTimeString();
                $indicators[] = $indicator;
            }
            ClientPoc::insert($indicators);
            return true;
        }
        return false;
    }

    public function deleteSalesPoc($where)
    {
        ClientPoc::where($where)->delete();
    }
}
