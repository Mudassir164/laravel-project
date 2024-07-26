<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{

    private $userSetting;

    public function __construct(UserSetting $userSetting)
    {
        $this->userSetting  =   $userSetting;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function userSetting()
    {
        try {
            $data    =   $this->userSetting->getUserSetting(['user_id' => auth()->user()->id]);
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function userSettingUpdate(Request $request)
    {
        try {
            $request->merge(['user_id' => auth()->user()->id]);
            $data    =   $this->userSetting->storeAndUpdateUserSetting(
                [
                    'user_id' => auth()->user()->id
                ],
                $request
            );
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function getUserSetting()
    {
        try {
            $data    =   $this->userSetting->getUserSetting(
                [
                    'user_id' => auth()->user()->id
                ]
            );
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
