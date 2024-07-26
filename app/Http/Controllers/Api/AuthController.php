<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\{
    BiometricLoginRequest,
    RegisterUserRequest,
    LoginUserRequest,
    ForgotPasswordRequest,
    ResetPasswordRequest,
    SignUpRequest,
    UpdateUserProfileRequest,
    ChangePasswordRequest,
};
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private $user;
    private $userReward;
    public function __construct(User $user, UserReward $userReward)
    {
        $this->user = $user;
        $this->userReward = $userReward;
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->merge(['league_id' => 1]);
            $data = $this->user->register($request);
            $response = compact('data');
            $response['message'] = 'User Registered';
            DB::commit();
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            DB::rollBack();
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function signUp(SignUpRequest $request)
    {
        try {
            $email = $request->get('email');
            $data = $this->user->signUp($email);
            $response = compact('data');
            $response['message'] = 'User Signed Up. Otp sent';
            return $this->responseToClient($response);
        }  catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function login(LoginUserRequest $request)
    {
        try {
            $credentials = $request->only(['email','password']);
            $data = $this->user->login($credentials);
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function userProfile()
    {
        try {
            $data = $this->user->getFullUserData(auth()->id());
            $response = compact('data');
            $response['message'] = 'current User';
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function userProfileUpdate(UpdateUserProfileRequest $request)
    {
        try {
            $data = $this->user->userProfileUpdate($request);
            $set = ['message' => 'Profile updated successfully.','data' => $data];
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $data = $this->user->changePassword($request);
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function logout(Request $request)
    {
        try {
            $response = $this->user->logout($request);
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $response = $this->user->forgotPassword($request);
            $response['message'] = 'Password reset OTP sent successfully.';
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $this->user->resetPassword($request);
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function biometric_login(BiometricLoginRequest $request)
    {
        try {
            $data = $this->user->biometric_login($request);
            $response = compact('data');
            $response['message'] = 'Biometrics has been enabled successfully.';
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function userRewards()
    {
        try {
            $data = $this->userReward->getAllRewards(['user_id' => auth()->user()->id]);
            $response = compact('data');
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }


    /* public function FcmTest()
    {
        // Path to your service account JSON file
        $serviceAccountPath = base_path('play-active-as-firebase-token.json');

        try {
            // Generate the OAuth token
            $token = $this->fcmAuthToken->getOAuthToken($serviceAccountPath);

            // Return the token as a response (for testing purposes)
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            // Handle exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } */
}


