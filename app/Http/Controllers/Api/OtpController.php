<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Models\UserOtp;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    private $otp;

    public function __construct(UserOtp $userOtp)
    {
        $this->otp = $userOtp;
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $data = $this->otp->verify($request);
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $user = auth()->user();
            $authUser = $request->user();
            $abilities = $authUser->currentAccessToken()->abilities;
            $ability = collect($abilities)->first();
            switch ($ability) {
                case 'email-otp':
                    $this->otp->sendOtp($user);
                    break;
                case 'password-otp':
                    $this->otp->sendPasswordOtp($user);
                    break;
                case 'reset-password':
                    $this->otp->sendPasswordOtp($user);
                    break;
                default:
                    throw new \Exception('Something went wrong');
                    break;
            }
            $data['message'] = 'Otp resent success!';
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
