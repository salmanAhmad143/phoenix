<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Interfaces\ClientPocRepositoryInterface;
use App\Repositories\Interfaces\ClientOperationalPocRepositoryInterface;
use App\Helpers\common;

class ClientController extends Controller
{
    private $clientRepository;
    private $clientSalesPoc;
    private $clientOperationalPoc;

    public function __construct(ClientRepositoryInterface $clientRepository, ClientPocRepositoryInterface $clientSalesPoc, ClientOperationalPocRepositoryInterface $clientOperationalPoc)
    {
        $this->clientRepository = $clientRepository;
        $this->clientSalesPoc = $clientSalesPoc;
        $this->clientOperationalPoc = $clientOperationalPoc;
        $this->common = new common();
    }


    /**
     * URL: api/v1/client
     */
    public function index(Request $request)
    {
        try {
            $param = array();
            $where = array();
            $size = intval($request->input("size"));
            if ($size == 0 || $size > config('constant.MAX_PAGE_SIZE')) {
                $size = config('constant.MAX_PAGE_SIZE');
            }
            if (!empty($request->clientName) && !in_array($request->clientName, ['{clientName}', 'undefined'])) {
                $where[] = ['clientName', 'like', '%' . $request->clientName . '%'];
            }
            $param['where'] = $where;
            $param['orderBy'] = ['clientId' => 'DESC'];
            $param['size'] = $size;
            $clients = $this->clientRepository->listClient($param);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/client',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        return response()->json([
            "success" => true,
            "data" => ['clients' => $clients],
        ]);        
    }

    /**
     * URL: api/v1/client/create
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'clientName' => 'required|string|unique:clients,clientName',
                    'country' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }            
            DB::beginTransaction();
            if (!empty($request->projectManager)) {
                $idParam['id'] = $request->projectManager;
                $idParam['errorMeg'] = "constant.PROJECT_MANAGER_NOT_EXIST_MESSAGE";
                $param['indicator']['projectManager'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->projectLead)) {
                $idParam['id'] = $request->projectLead;
                $idParam['errorMeg'] = "constant.PROJECT_LEAD_NOT_EXIST_MESSAGE";
                $param['indicator']['projectLead'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->salesRep)) {
                $idParam['id'] = $request->salesRep;
                $idParam['errorMeg'] = "constant.SALES_REP_ID_INCORRECT_MESSAGE";
                $param['indicator']['salesRep'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->country)) {
                $idParam['id'] = $request->country;
                $idParam['errorMeg'] = "constant.COUNTRY_ID_INCORRECT_MESSAGE";
                $param['indicator']['country'] = $this->common->getDecodeId($idParam);
            }
            $param['indicator']['clientName'] = $request->clientName ?? null;
            $param['indicator']['address'] = $request->address ?? null;
            $param['indicator']['city'] = $request->city ?? null;
            $param['indicator']['postalCode'] = $request->postalCode ?? null;
            $param['indicator']['createdBy'] = auth()->user()->userId;
            $client = $this->clientRepository->addClient($param);

            if ($client) {
                if (!empty($request->salesPocs)) {
                    //Inserrting sales pocs.
                    $clientSalesPoc = $this->clientSalesPoc->addSalesPoc([
                        'salesPoc' => $request->salesPocs,
                        'clientId' => $client->clientId                   
                    ]);
                }
                if (!empty($request->operationalPocs)) {
                    //Inserting operational pocs.
                    $this->clientOperationalPoc->addOperationalPoc([
                        'operationalPoc' => $request->operationalPocs,
                        'clientId' => $client->clientId              
                    ]);
                }
            }

        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/client/create',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.CLIENT_CREATE_MESSAGE'),
            "data" => [
                'clientId' => $client->id
            ]
        ]);
    }

    /**
     * URL: api/v1/client/update
     */
    public function update(Request $request)
    {
        try {
            if (!empty($request->id)) {
                $idParam['id'] = $request->id;
                $idParam['errorMeg'] = "constant.CLIENT_ID_INCORRECT_MESSAGE";
                $clientId = $this->common->getDecodeId($idParam);
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'clientName' => 'required|string|unique:clients,clientName,'.$clientId.',clientId',
                    'country' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            
            $client = $this->clientRepository->getClient(['clientId' => $clientId]);
            if ($client == null) {
                throw new CustomException(config('constant.CLIENT_NOT_EXIST_MESSAGE'));
            }
            
            DB::beginTransaction();
            $param['indicator'] = [
                'projectManager' => null,
                'projectLead' => null,
                'salesRep' => null
            ];
            //Updating client records.
            if (!empty($request->projectManager)) {
                $idParam['id'] = $request->projectManager;
                $idParam['errorMeg'] = "constant.PROJECT_MANAGER_NOT_EXIST_MESSAGE";
                $param['indicator']['projectManager'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->projectLead)) {
                $idParam['id'] = $request->projectLead;
                $idParam['errorMeg'] = "constant.PROJECT_LEAD_NOT_EXIST_MESSAGE";
                $param['indicator']['projectLead'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->salesRep)) {
                $idParam['id'] = $request->salesRep;
                $idParam['errorMeg'] = "constant.SALES_REP_ID_INCORRECT_MESSAGE";
                $param['indicator']['salesRep'] = $this->common->getDecodeId($idParam);
            }
            if (!empty($request->country)) {
                $idParam['id'] = $request->country;
                $idParam['errorMeg'] = "constant.COUNTRY_ID_INCORRECT_MESSAGE";
                $param['indicator']['country'] = $this->common->getDecodeId($idParam);
            }
            $param['indicator']['clientName'] = $request->clientName ?? null;
            $param['indicator']['address'] = $request->address ?? null;
            $param['indicator']['city'] = $request->city ?? null;
            $param['indicator']['postalCode'] = $request->postalCode ?? null;
            $param['indicator']['updatedBy'] = auth()->user()->userId;
            $param['where']['clientId'] = $client->clientId;
            $this->clientRepository->updateClient($param);

            $this->clientSalesPoc->deleteSalesPoc(['clientId' =>$client->clientId]);
            $this->clientOperationalPoc->deleteOperationalPoc(['clientId' =>$client->clientId]);

            if (!empty($request->salesPoc)) {
                //Adding sales point of contact users.
                $this->clientSalesPoc->addSalesPoc([
                    'clientId' => $client->clientId,
                    'salesPoc' => $request->salesPoc
                ]);
            }
            if (!empty($request->operationalPoc)) {
                //Adding operational point of contact users.
                $this->clientOperationalPoc->addOperationalPoc([
                    'clientId' => $client->clientId,
                    'operationalPoc' => $request->operationalPoc
                ]);
            }

        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/client/update',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.CLIENT_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/client/details
     */
    public function show(Request $request)
    {
        try {
            if (count(Hashids::decode($request->clientId)) === 0) {
                throw new CustomException(config('constant.CLIENT_ID_INCORRECT_MESSAGE'));
            }
            $client = $this->clientRepository->getClient([
                'clientId' => Hashids::decode($request->clientId),
            ]);
            if ($client === null) {
                throw new CustomException(config('constant.CLIENT_NOT_EXIST_MESSAGE'));
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/client/details',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => [
                'clients' => $client,
            ],
        ]);
    }

    /**
     * URL: api/v1/client/delete
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            if (count(Hashids::decode($request->clientId)) === 0) {
                throw new CustomException(config('constant.CLIENT_ID_INCORRECT_MESSAGE'));
            }
            $client = $this->clientRepository->getClient([
                'clientId' => Hashids::decode($request->clientId)
            ]);
            if ($client === null) {
                throw new CustomException(config('constant.CLIENT_NOT_EXIST_MESSAGE'));
            }

            //delete sales poc before client delete
            $this->clientSalesPoc->deleteSalesPoc([
                    'clientId' => $client->clientId
                ]);
            //delete Operational poc before client delete
            $this->clientOperationalPoc->deleteOperationalPoc([
                        'clientId' => $client->clientId
                    ]);
            $this->clientRepository->deleteClient([
                'clientId' => $client->clientId
            ]);
        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/client/delete',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.CLIENT_DELETE_MESSAGE'),
        ]);
    }
}
