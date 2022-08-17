<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Client;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Helpers\common;

class ClientRepository implements ClientRepositoryInterface
{
    public function listClient($param)
    {
        $client = Client::query();
        $client->with('projectLeadDetail:userId,name,email');
        $client->with('projectManagerDetail:userId,name,email');
        $client->with('salesRepDetail:userId,name,email');
        $client->with('createdBy:userId,name,email'); 
        $client->with('countryDetail:id,codeValue as name');
        $client->with(['salesPoc' => function($salePoc) {
            $salePoc->select(['userId', 'clientId']);
            $salePoc->with('pocUserDetail:userId,name,email');
        }]);

        $client->with(['operationalPoc' => function($operationalPoc) {
            $operationalPoc->select(['userId', 'clientId']);
            $operationalPoc->with('pocUserDetail:userId,name,email');
        }]);

        if (isset($param['where']) && count($param['where']) > 0) {
            $client->where($param['where']);
        }
        if ( !empty(auth()->user()->accessLevel->codeValue) && auth()->user()->accessLevel->codeValue == "Hierarchy" ) {

            $employeeDept = auth()->user()->department->codeValue;
            if (in_array($employeeDept, common::salesRelatedDept)) {
                $ids = common::getUserAccessIds();
                $pocClientIds = common::getPocsClientIds($ids['arrayValue']);
                $opocClientIds = common::getOpocsClientIds($ids['arrayValue']);
                //Adding condition for the client access check.
                $client->orwhereIn('clients.salesRep', $ids['arrayValue']);
                $client->orwhereIn('clients.projectManager', $ids['arrayValue']);
                $client->orwhereIn('clients.projectLead', $ids['arrayValue']);
                $client->orwhereIn('clients.createdBy', $ids['arrayValue']);
                //Adding condition for the additional poc access check.
                if (!empty($pocClientIds['poc_clientIds'])) {
                    $client->orWhereIn('clients.clientId', explode(",", $pocClientIds['poc_clientIds']));
                }

                //Adding condition for the Operational poc access check.
                if (!empty($opocClientIds['opoc_clientIds'])) {
                    $client->orWhereIn('clients.clientId', explode(",", $opocClientIds['opoc_clientIds']));
                }
            } else {
                $pocClientIds = common::getPocsClientIds([auth()->user()->userId]);
                $opocClientIds = common::getOpocsClientIds([auth()->user()->userId]);
                $client->orwhere('clients.salesRep', auth()->user()->userId);
                $client->orwhere('clients.projectManager', auth()->user()->userId);
                $client->orwhere('clients.projectLead', auth()->user()->userId);
                $client->orwhere('clients.createdBy', auth()->user()->userId);
                if (!empty($pocClientIds['poc_clientIds'])) {
                    $client->orWhereIn('clients.clientId', explode(",", $pocClientIds['poc_clientIds']));
                }
                //Adding condition for the Operational poc access check.
                if (!empty($opocClientIds['opoc_clientIds'])) {
                    $client->orWhereIn('clients.clientId', explode(",", $opocClientIds['opoc_clientIds']));
                }
            }
        }
        return $client->orderBy('clientId', 'DESC')->paginate($param['size']);
    }

    public function getClient($where)
    {
        return Client::where($where)->first();
    }

    public function addClient($param)
    {
        return Client::create($param['indicator']);
    }

    public function updateClient($param)
    {
        $client = Client::where($param['where'])->first();
        $client->fill($param['indicator']);
        $client->save();
    }

    public function deleteClient($where)
    {
        return Client::where($where)->delete();
    }
}
