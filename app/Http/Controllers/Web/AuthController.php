<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use Illuminate\Http\Request;
use App\Http\Requests\Web\{
    ChangePasswordRequest,
    LoginUserRequest,
    RegisterPubOwnerRequest,
    RegisterSponsorRequest,
    ForgotPasswordRequest,
    ResetPasswordRequest,
    UpdateSponsorProfileRequest,
    UpdatePubOwnerProfileRequest,
    UpdateOrganizerProfileRequest,
    SignUpRequest,
};
use App\Traits\StripeTrait;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use StripeTrait;

    private $user;

    public function __construct(WebUser $webUser)
    {
        $this->user = $webUser;
    }

    public function signUp(SignUpRequest $request)
    {
        try {
            $role = $request->route()->getName();
            $email = $request->get('email');
            $data = $this->user->signUp($email,$role);
            $response = compact('data');
            $response['message'] = 'User Signed Up. Otp sent';
            return $this->responseToClient($response);
        }  catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function registerPubOwner(RegisterPubOwnerRequest $request)
    {
        try {
            DB::beginTransaction();
            if ($request->token && $request->subscriptionId) {
                $stripeToken = $request->input('token');  // Stripe card token
                $priceId = $request->input('subscriptionId');   // Subscription product price ID
                $userSubscription = $this->createSubscription($stripeToken, $priceId);
            }
            $data = $this->user->registerPubOwner($request);
            $response = compact('data');
            $response['message'] = 'Pub Owner Registered';
            DB::commit();
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            DB::rollBack();
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function registerSponsor(RegisterSponsorRequest $request)
    {
        try {
            $data = $this->user->registerSponsor($request);
            $response = compact('data');
            $response['message'] = 'Sponsor Registered';
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $credentials = $request->only(['email','password']);
            $data = $this->user->login($credentials,$request->device_token);
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function userProfile()
    {
        try {
            $data = $this->user->with(['city','country','role'])->find(auth()->id());
            $response = compact('data');
            $response['message'] = 'current User';
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function pubOwnerProfileUpdate(UpdatePubOwnerProfileRequest $request)
    {
        try {
            $data = $this->user->pubOwnerProfileUpdate($request);
            $set = ['message' => 'Profile updated successfully.','data' => $data];
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function sponsorProfileUpdate(UpdateSponsorProfileRequest $request)
    {
        try {
            $data = $this->user->sponsorProfileUpdate($request);
            $set = ['message' => 'Profile updated successfully.','data' => $data];
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

    public function organizerProfileUpdate(UpdateOrganizerProfileRequest $request)
    {
        try {
            $data = $this->user->organizerProfileUpdate($request);
            $set = ['message' => 'Profile updated successfully.','data' => $data];
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $response = $this->user->forgotPassword($request);
            $response['message'] = 'Password Reset Otp Sent';
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
            $user = $this->user::find(auth()->id());
            $user->device_token = null;
            $user->save();
            $response = $this->user->logout($request);
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()],400);
        }
    }

}
