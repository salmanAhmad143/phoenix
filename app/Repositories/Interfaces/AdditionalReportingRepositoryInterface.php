<?php

namespace App\Repositories\Interfaces;

interface AdditionalReportingRepositoryInterface
{
    public function listAdditionalReporting($param);

    public function getAdditionalReporting($where);

    public function addAdditionalReporting($param);

    public function updateAdditionalReporting($param);

    public function deleteAdditionalReporting($where);

    public function deleteMultipleAdditionalReporting($where);
}
