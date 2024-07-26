<?php

namespace App\Models;

use App\Mail\{OtpMail, PasswordOtpMail, PubOwnerPasswordMail};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserOtp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['ref','user_id', 'code', 'expires_at'];

    public function user()
    {
        return $this->morphTo();
    }

    public function sendOtp($user)
    {
        $code = rand(1000, 9999);
        $set = ['ref' => get_class($user),'user_id' => $user->id, 'code' => $code, 'expires_at' => now()->addMinutes(2)];
        $this::updateOrCreate(['user_id' => $user->id], $set);
        $mail = ['name' => $user->name, 'code' => $code, 'expires_at' => $set['expires_at']];
        Mail::to($user->email)->send(new OtpMail($mail));
    }

    // public function sendPassword($data)
    // {
    //     $mail = ['email' => $data->email, 'pub_name' => $data->pub_name, 'password' => $data->password];
    //     Mail::to($data->email)->send(new PubOwnerPasswordMail($mail));
    // }

    public function sendPasswordOtp($user)
    {
        $code = rand(1000, 9999);
        $set = ['ref' => get_class($user),'user_id' => $user->id, 'code' => $code, 'expires_at' => now()->addMinutes(2)];
        $this::updateOrCreate(['user_id' => $user->id], $set);
        $mail = ['name' => $user->name, 'code' => $code, 'expires_at' => $set['expires_at']];
        Mail::to($user->email)->send(new PasswordOtpMail($mail));
    }

    public function verify(Request $request)
    {
        $authUser = $request->user();
        $ref = get_class($authUser);
        $abilities = $authUser->currentAccessToken()->abilities;
        $ability = collect($abilities)->first();
        $code = $this->where(['ref' => $ref, 'user_id' => auth()->id(), 'code' => $request->code])
            ->where('expires_at', '>=', now())
            ->first();
        if ($code) {
            return $this->markUserVerified($code,$ability,$ref);
        }
        throw new \Exception('OTP is expired!');
    }


    public function markUserVerified($code,$ability,$ref)
    {
        $add_scope = ($ref == 'App\Models\WebUser') ? '-web' : '';
        $authUser = request()->user();
        $user = $ref::find(auth()->id());
        $data = $user;
        $data = compact('data');
        switch ($ability) {
            case 'email-otp':
                $scope = 'register';
                $this::find($code->id)->delete();
                $authUser->currentAccessToken()->delete();
                $token = $user->createToken('SignUp', [$scope.$add_scope])->plainTextToken;
                $user->{'token'} = $token;
                $data['message'] = 'Otp verification succes';
                break;
            case 'password-otp':
                $scope = 'reset-password';
                $this::find($code->id)->delete();
                $authUser->currentAccessToken()->delete();
                $token = $user->createToken('OTP', [$scope.$add_scope])->plainTextToken;
                $user->{'token'} = $token;
                $data['data'] = $user;
                $data['message'] = 'Password Otp verification succes';
                break;
            default:
                throw new \Exception('Something went wrong');
                break;
        }
        return $data;
    }
}
