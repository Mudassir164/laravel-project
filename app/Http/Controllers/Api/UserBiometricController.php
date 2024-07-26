<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserBiometricRequest;
use App\Models\UserBiometric;
use Illuminate\Http\Request;

class UserBiometricController extends Controller
{

    private $userBiometric;

    public function __construct(UserBiometric $userBiometric)
    {
        $this->userBiometric    =   $userBiometric;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function userBiometric(UserBiometricRequest $request)
    {
        try {
            $data = $this->userBiometric->storeAndUpdateUserBiometric($request);
            return $this->responseToClient(['message' => 'Biometrics has been enabled successfully. ', 'data' => $data]);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserBiometric  $userBiometric
     * @return \Illuminate\Http\Response
     */
    public function show(UserBiometric $userBiometric)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserBiometric  $userBiometric
     * @return \Illuminate\Http\Response
     */
    public function edit(UserBiometric $userBiometric)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserBiometric  $userBiometric
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserBiometric $userBiometric)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserBiometric  $userBiometric
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserBiometric $userBiometric)
    {
        //
    }
}
