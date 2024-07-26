<?php

namespace App\Models;

use App\Mail\{
    SendOrganizerPasswordMail,
    PubOwnerPasswordMail,
};
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Storage, Hash, Mail};
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WebUser extends User
{
    use HasFactory, SoftDeletes, HasApiTokens, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'parent_id',
        'name',
        'user_name',
        'pub_name',
        'owner',
        'email',
        'phone',
        'address',
        'password',
        'device_token',
        'country_id',
        'city_id',
        'profile_pic',
        'post_code',
        'business_name',
        'status',
        'email_verified_at',
        'qr_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_pic_url',
        'qr_code_url',
        'organizers_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function eventsCreated()
    {
        return $this->hasMany(Game::class, 'web_user_id', 'id');
    }

    public function password(): Attribute
    {
        return Attribute::set(fn($val) => Hash::make($val));
    }

    public function profilePicUrl(): Attribute
    {
        return Attribute::get(fn() => $this->profile_pic ? Storage::url($this->profile_pic) : null);
    }

    public function qrCodeUrl(): Attribute
    {
        return Attribute::get(fn() => $this->qr_code ? Storage::url($this->qr_code) : null);
    }

    public function organizersCreated()
    {
        return $this->hasMany(WebUser::class, 'parent_id');
    }

    public function organizersCount(): Attribute
    {
        return Attribute::get(fn() => $this->organizersCreated()->count());
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')->with(['webRoutes']);
    }

    public function otp()
    {
        return $this->morphOne(UserOtp::class, 'user', 'ref');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'user', 'ref');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function signUp($email, $role)
    {
        switch ($role) {
            case 'pub-owner':
                $roleId = Role::PUB_OWNER;
                break;
            case 'sponsor':
                $roleId = Role::SPONSOR;
                break;
            default:
                throw new \InvalidArgumentException('Invalid role provided');
        }

        $set = ['role_id' => $roleId, 'email' => $email];
        $user = $this::updateOrCreate(['email' => $email], $set);

        $token = $user->createToken('OTP', ['email-otp'])->plainTextToken;
        $otp = new UserOtp;
        $otp->sendOtp($user);

        $user->{'token'} = $token;

        return $user;
    }

    public function registerPubOwner(Request $request)
    {
        if (!auth()->user()->qr_code) {
            $qrCode = QrCode::format('png')->size(300)->generate('pub_id_' . auth()->user()->id);
            $fileName = 'qr_code/' . rand('00000', '99999') . '_' . time() . '.png';
            Storage::put($fileName, $qrCode);
            $request->merge(['qr_code' => $fileName]);
        }
        $this::find(auth()->id())->update($request->only($this->getFillable()));
        $user = $this::find(auth()->id());
        $user->email_verified_at = now();
        $user->save();
        $token = $user->createToken('Auth', ['web'])->plainTextToken;
        $user->{'token'} = $token;
        $data = $this->getFullUserData($user->id);
        $data->{'token'} = $token;
        return $data;
    }

    public function addPubOwner(Request $request)
    {
        $password = Str::random(10);
        $request->merge(['parent_id' => auth()->id(), 'role_id' => Role::PUB_OWNER, 'password' => $password, 'email_verified_at' => now()]);
        $data = $request->only($this->getFillable());
        $user = $this->create($data);
        Mail::to($user->email)->send(new PubOwnerPasswordMail($data));
        return $user;
    }

    public function registerSponsor(Request $request)
    {
        $this::find(auth()->id())->update($request->only($this->getFillable()));
        $user = $this::find(auth()->id());
        $user->email_verified_at = now();
        $user->save();
        $token = $user->createToken('Auth', ['web'])->plainTextToken;
        $user->{'token'} = $token;
        $data = $this->getFullUserData($user->id);
        $data->{'token'} = $token;
        return $data;
    }

    public function registerOrganizer(Request $request)
    {
        $request->merge(['parent_id' => auth()->id(), 'role_id' => Role::ORGANIZER, 'email_verified_at' => now()]);
        $data = $request->only($this->getFillable());
        $user = $this->create($data);
        Mail::to($user->email)->send(new SendOrganizerPasswordMail($data));
        return $user;
    }

    public function updateOrganizerStatus($id, $data = [])
    {
        return $this->find($id)->update($data);
    }

    public function updateOrganizer($id, Request $request)
    {
        $data = $this->getFullUserData($id);
        if (!$data) {
            throw new Exception('Organizer not found!', 404);
        }
        $this->find($id)->update($request->only($this->getFillable()));
        $data = $this->getFullUserData($id);
        $response = compact('data');
        return $response;
    }

    public function deleteOrganizer($id)
    {
        $data = $this->getFullUserData($id);
        if (!$data) {
            throw new Exception('Organizer not found!', 404);
        }
        $this->find($id)->delete();
        $response['message'] = 'Organizer deleted successfully!';
        return $response;
    }

    public function changePassword(Request $request)
    {
        $data = $this::find(auth()->id());
        $data->password = $request->get('new_password');
        $data->save();
        $set = compact('data');
        $set['message'] = 'user password changed';
        return $set;
    }

    public function pubOwnerProfileUpdate(Request $request)
    {
        $profile_pic = null;
        $user = $this::find(auth()->id());
        if ($request->has('profile_pic') && $request->profile_pic) {
            $profile_pic = $this->uploadProfilePic($request);
            $user->profile_pic = $profile_pic;
        }
        $user->pub_name = $request->pub_name;
        $user->owner = $request->owner;
        $user->address = $request->address;
        $user->country_id = $request->country_id;
        $user->city_id = $request->city_id;
        $user->phone = $request->phone;
        $user->post_code = $request->post_code;
        $user->save();
        return $this->getFullUserData(auth()->id());
    }

    public function sponsorProfileUpdate(Request $request)
    {
        $profile_pic = null;
        $user = $this::find(auth()->id());
        if ($request->has('profile_pic') && $request->profile_pic) {
            $profile_pic = $this->uploadProfilePic($request);
            $user->profile_pic = $profile_pic;
        }
        $user->business_name = $request->business_name;
        $user->owner = $request->owner;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->country_id = $request->country_id;
        $user->city_id = $request->city_id;
        $user->post_code = $request->post_code;
        $user->save();
        return $this->getFullUserData(auth()->id());
    }

    public function organizerProfileUpdate(Request $request)
    {
        $profile_pic = null;
        $user = $this::find(auth()->id());
        if ($request->has('profile_pic') && $request->profile_pic) {
            $profile_pic = $this->uploadProfilePic($request);
            $user->profile_pic = $profile_pic;
        }
        $user->name = $request->name;
        $user->user_name = $request->user_name;
        $user->phone = $request->phone;
        $user->save();
        return $this->getFullUserData(auth()->id());
    }

    public function forgotPassword(Request $request)
    {
        $user = $this::where('email', $request->email)->first();
        $token = $user->createToken('OTP', ['password-otp'])->plainTextToken;
        $data = ['token' => $token];
        $otp = new UserOtp;
        $otp->sendPasswordOtp($user);
        return $data;
    }

    public function login($credentials,$device_token)
    {
        if (auth('panel')->attempt($credentials)) {
            $user = $this::where('email', $credentials['email'])->first();
            $user->device_token = $device_token;
            $user->save();
            $token = $user->createToken('Auth', ['web'])->plainTextToken;
            $data = $this->getFullUserData($user->id);
            $data->{'token'} = $token;
            return $data;
        }
        throw new \Exception('Invalid credentials!');
    }
    public function resetPassword(Request $request)
    {
        $authUser = $request->user();
        $token = $authUser->createToken('Auth', ['web'])->plainTextToken;
        $data = $this::find($authUser->id);
        $data->password = $request->password;
        $data->save();
        $authUser->currentAccessToken()->delete();
        $data = $this->getFullUserData($authUser->id);
        $data['token'] = $token;
        $responseData = [
            'data' => $data,
            'message' => 'Password Reset Success'
        ];
        return $responseData;
    }

    public function getFullUserData($id)
    {
        return $this->with('city', 'country', 'role')->find($id);
    }
    public function updateUser($id, $data = [])
    {
        return $this->find($id)->update($data);
    }

    public function getAllOrganizer($where = [], $filters = [])
{
    return $this->with(['role'])
        ->where('role_id', 4)
        ->where($where)
        ->withCount(['eventsCreated as eventCreated' => function ($query) {
            $query->select(\DB::raw('count(*)'));
        }])
        ->when(count($filters) > 0, function ($query) use ($filters) {
            $query->when(!empty($filters['search']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            });

            if (!empty($filters['key']) && !empty($filters['order'])) {
                $key = $filters['key'];
                $order = strtolower($filters['order']);

                if (in_array($order, ['asc', 'desc']) && in_array($key, ['name', 'email', 'user_name','created_at'])) {
                    $query->orderBy($key, $order);
                }
            }
        });
}

    public function logout(Request $request)
    {
        $data = $request->user();
        $data->currentAccessToken()->delete();
        $response = compact('data');
        $response['message'] = 'user logged out!';
        return $response;
    }

    private function uploadProfilePic(Request $request)
    {
        $currentPic = auth()->user()->profile_pic;
        if ($currentPic)
            Storage::delete($currentPic);
        $file = $request->file('profile_pic');
        $fileName = auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $uploaded = Storage::putFileAs('web-profile-pictures', $file, $fileName);
        return $uploaded;
    }
}
