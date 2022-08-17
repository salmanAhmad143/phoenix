<?php
namespace App\Helpers;
use Exception;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\DB;
use App\Notifications\Exception as NotifyException;
use App\Models\User;
use App\Models\ClientPoc;
use App\Models\Client;
use App\Models\ClientOperationalPoc;

class common
{
    //This function is use to get the hirerachy list id.
    function getDecodeId($params)
    {
        $decodeId = Hashids::decode($params['id']);
        if (count($decodeId) === 0) {
            throw new CustomException(config($params['errorMeg']));
        }
        return $decodeId[0];
    }

    //Define department for the hierarchy applied in project.
    const salesRelatedDept = ['Sales Management'];

    //This function is use to getting the id's of the user's that have this user access.
    public static function getUserAccessIds()
    {
        $result = array();
        $obj = new common();
        $user = auth()->user();
        $reportingList = [$user->userId];
        $data = $user->reportingUsers;
        foreach ($data as $value) {
            $reportingList[] = $value->userId;
        }
        $ids = $obj->getReportingHirerachy($reportingList, $reportingList);
        $result['arrayValue'] = $ids;
        $ids = (count($ids) > 0) ? implode(',', $ids) : 0;
        $result['stringValue'] = $ids;
        return $result;
    }

    //This function is use to get the hirerachy list id.
    function getReportingHirerachy($emp_id = 0, &$reportingArray)
    {
        $employees = User::select('userId')->whereIn('reportingManagerId', $emp_id)->get();
        if ( count($employees) ==0 ) {
            return $reportingArray;
        } else {
            foreach($employees as $employee) {
                array_push($reportingArray, $employee->userId);
                $this->getReportingHirerachy(array($employee->userId), $reportingArray);
            }
            return array_unique($reportingArray);
        }
    }

    //This function is use to getting the id's point of contacts.
    public static function getPocsClientIds($userIds)
    {
        $clientPocIds = ClientPoc::select(DB::raw('GROUP_CONCAT(clientId) as poc_clientIds'))
            ->whereIn("userId", $userIds)
            ->first();
        return $clientPocIds;
    }


    //This function is use to getting the id's point of contacts.
    public static function getOpocsClientIds($userIds)
    {
        $clientOpocIds = ClientOperationalPoc::select(DB::raw('GROUP_CONCAT(clientId) as opoc_clientIds'))
            ->whereIn("userId", $userIds)
            ->first();
        return $clientOpocIds;
    }

    //This function is use to getting the client's id of user.
    public static function getClientIds($userIds)
    {
        $client = Client::select(DB::raw('GROUP_CONCAT(clientId) as clientIds'))
            ->whereIn("salesRep", $userIds)
            ->orwhereIn("projectManager", $userIds)
            ->orwhereIn("projectLead", $userIds)->first();
        return $client;
    }

}