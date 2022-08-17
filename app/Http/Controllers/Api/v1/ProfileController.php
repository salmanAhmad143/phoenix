<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;

use App\Model\Profile;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Log::useDailyFiles(storage_path() . '/logs/profile.log');
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string',
                    'email' => 'required|string|email|unique:profile,primaryEmail',
                    'password' => 'required|string|confirmed',
                ]
            );
            if ($validator->fails()) {
                $msg = $validator->errors()->first();
                Log::error($msg);
                throw new Exception($msg);
            }
            Log::info("Request validated");
            DB::beginTransaction();
            // $profile = Profile::where('primaryEmail', $request->email)->get();
            // if (count($profile) > 0) {
            //     $msg = "Email ".$request->email." already exist";
            //     Log::error($msg);
            //     throw new Exception($msg);
            // }
            // Log::info("Email checked and ready to be inserted in database");
            $profile = new Profile();
            $profile->primaryEmail = $request->email;
            if (!$profile->save()) {
                Log::error("Email not saved in database");
                throw new Exception("Something went wrong");
            }
            Log::info("Email " . $request->email . " has been saved in profile table");

            $userLogin = new UserLogin();
            $userLogin->profileId = $profile->profileId;
            $userLogin->name = $request->name;
            $userLogin->email = $request->email;
            $userLogin->password = Hash::make($request->password);
            $userLogin->api_token = Str::random(60);
            if (!$userLogin->save()) {
                Log::error("User login not saved");
                throw new Exception("Something went wrong");
            }
            Log::info("User login saved successfully");
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                "status" => 0,
                "msg" => $e->getMessage(),
            ]);
        }

        DB::commit();
        return response()->json([
            "status" => 1,
            "msg" => "User has been registered successfully",
        ]);
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
