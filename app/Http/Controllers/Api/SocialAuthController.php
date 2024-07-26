<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SocialLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login(SocialLoginRequest $request)
    {
        try {
            $device_token = $request->get('device_token');
            $customEmail = $request->get('platform_id') . '@pubactive.com';
            if (!$request->has('email') && is_null($request->email)) $request->merge(['email' => $customEmail]);
            $current_user = $this->user::select(
                ['id','type','platform_id','name','device_token','updated_at','created_at']
                )->where('platform_id', $request->get('platform_id'))->first();
            if ($current_user) {
                $current_user->device_token = $device_token;
                $current_user->save();
                $token = $current_user->createToken('Auth')->plainTextToken;
                $current_user->{'token'} = $token;
                $user = $current_user;
            } else {
                $user = $this->user;
                $user->type = $request->get('type');
                $user->platform_id = $request->get('platform_id');
                $user->email = $request->get('email');
                $user->name = $request->get('name');
                $user->profile_pic = $request->get('image');
                $user->device_token = $device_token;
                $user->save();
                $token = $user->createToken('Auth')->plainTextToken;
                $user->{'token'} = $token;
            }
            $data = $user;
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
