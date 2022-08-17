<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\LanguageRepositoryInterface;

class LanguageController extends Controller
{
    private $languageRepository;

    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/language
     */
    public function index(Request $request)
    {
        try {
            $param = array();
            $where = array();
            $orWhere = array();
            if ($request->input('searchValue') && !in_array($request->input('searchValue'), ['{searchValue}', 'undefined'])) {
                $where[] = ['language', 'like', '%' . $request->input('searchValue') . '%'];
                $orWhere[] = ['languageCode', 'like', '%' . $request->input('searchValue') . '%'];
            }
            if ($request->input('language') && !in_array($request->input('language'), ['{language}', 'undefined'])) {
                $where[] = ['language', 'like', '%' . $request->input('language') . '%'];
            }
            if ($request->input('languageCode') && !in_array($request->input('languageCode'), ['{languageCode}', 'undefined'])) {
                $where[] = ['languageCode', 'like', '%' . $request->input('languageCode') . '%'];
            }
            if ($request->input('languageFor') && !in_array($request->input('languageFor'), ['{languageFor}', 'undefined'])) {
                $where['languageFor'] = $request->input('languageFor');
            }
            if ($request->input('autoTranslate') && !in_array($request->input('autoTranslate'), ['{autoTranslate}', 'undefined'])) {
                $where['autoTranslate'] = $request->input('autoTranslate');
            }
            if ($request->input('autoTranscribe') && !in_array($request->input('autoTranscribe'), ['{autoTranscribe}', 'undefined'])) {
                $where['autoTranscribe'] = $request->input('autoTranscribe');
            }
            if ($request->input('languageId') && !in_array($request->input('languageId'), ['{languageId}', 'undefined'])) {
                $where['languageId'] = $request->input('languageId');
            }
            if (count($where) > 0) {
                $param['where'] = $where;
            }
            if (count($orWhere) > 0) {
                $param['orWhere'] = $orWhere;
            }
            $languages = $this->languageRepository->listLanguage($param);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/language',
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
            "data" => ['languages' => $languages],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
