<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\{DB, Storage, Hash};
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;
use function App\Helpers\findCurrentPositionOfUserLeaderboard;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'profile_pic',
        'username',
        'name',
        'email',
        'password',
        'gender',
        'country_id',
        'city_id',
        'league_id',
        'status'
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
        //'highest_score'
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

    public function password(): Attribute
    {
        return Attribute::set(fn($val) => Hash::make($val));
    }

    public function profilePicUrl(): Attribute
    {
        return Attribute::get(fn() => $this->profile_pic ? Storage::url($this->profile_pic) : null);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    public function gameSubmissions()
    {
        return $this->hasMany(GameSubmission::class)->with('gameSubmissionDetails');
    }

    public function otp()
    {
        return $this->morphOne(UserOtp::class, 'user', 'ref');
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class, 'user_id');
    }

    public function gameFeedbacks()
    {
        return $this->hasMany(GameFeedback::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'user', 'ref');
    }

    public function leaderboard()
    {
        return $this->hasOne(UserLeaderboard::class, 'user_id')->select('user_id', 'score', )
            ->selectRaw('(SELECT COUNT(*) + 1
                      FROM user_leaderboards AS ul2
                      WHERE ul2.score > user_leaderboards.score) AS current_position', )
            ->from('user_leaderboards')
            ->orderByDesc('score');
        // return [
        //     'position' => $result->position ?? 0,
        //     'score' => (int) round($result->score) ?? 0,
        // ];
    }

    public function getAllUser($where = [], $filters = [])
    {
        return $this->with(['city', 'country', 'league'])->where($where)
            ->when(count($filters) > 0, function ($query) use ($filters) {
                $query->when(!empty($filters['search']), function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            });
    }

    public static function updateUser($id, $data = [])
    {
        return self::find($id)->update($data);
    }


    public function signUp($email)
    {
        $user = $this::updateOrCreate(['email' => $email], ['email' => $email]);
        $token = $user->createToken('OTP', ['email-otp'])->plainTextToken;
        $otp = new UserOtp;
        $otp->sendOtp($user);
        $user->{'token'} = $token;
        return $user;
    }

    public function register(Request $request)
    {
        $profile_pic = null;
        $device_token = $request->get('device_token');
        $this::find(auth()->id())->update($request->only($this->getFillable()));
        $user = $this::find(auth()->id());
        if ($request->has('profile_pic')) {
            $profile_pic = $this->uploadProfilePic($request);
        }
        $user->email_verified_at = now();
        $user->profile_pic = $profile_pic;
        $user->device_token = $device_token;
        $user->save();
        UserSetting::UpdateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'event_creation_time' => 1,
                'event_end_time' => 1,
                'all_notification' => 1
            ]
        );
        $token = $user->createToken('Auth')->plainTextToken;
        $user->{'token'} = $token;
        $data = $this->getFullUserData($user->id);
        $data->{'token'} = $token;
        return $data;
    }

    public function login($credentials)
    {
        $device_token = Request()->get('device_token');

        if (auth()->attempt($credentials)) {
            $key = 12345; // Simple key for demonstration purposes

            $user = $this::where('email', $credentials['email'])->first();
            $user->device_token = $device_token;
            $user->save();
            $token = $user->createToken('Auth')->plainTextToken;
            $data = $this->getFullUserData($user->id);
            $data->{'token'} = $token;
            $data->{'user_encrypted_id'} = $this->customEncryptToNumbers("$user->id", $key);
            return $data;
        }
        throw new \Exception('Invalid credentials!');
    }

    public function customEncryptToNumbers($data, $key) {
        // Generate a random salt
        $salt = random_int(10, 99); // Change this range as needed for more randomness

        // Convert the input string to a numeric form
        $numericData = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $numericData .= ord($data[$i]);
        }

        // XOR the numeric data with the key and the salt
        $encryptedData = $numericData ^ $key ^ $salt;

        // Combine the salt with the encrypted data for later use in decryption
        return $salt . $encryptedData;
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

    public function logout(Request $request)
    {
        $data = $request->user();
        $data->currentAccessToken()->delete();
        $response = compact('data');
        $response['message'] = 'user logged out!';
        return $response;
    }

    public function resetPassword(Request $request)
    {
        $authUser = $request->user();
        $data = $this::find($authUser->id);
        $data->password = $request->password;
        $data->save();
        $data = compact('data');
        $data['message'] = 'Password Reset Success';
        $authUser->currentAccessToken()->delete();
        $data['data'] = $this->getFullUserData($authUser->id);
        return $data;
    }

    public function userProfileUpdate(Request $request)
    {
        $profile_pic = null;
        $longitude = $request->get('longitude') ?? null;
        $latitude = $request->get('latitude') ?? null;
        $user = $this::find(auth()->id());
        if ($request->has('profile_pic') && $request->profile_pic) {
            $profile_pic = $this->uploadProfilePic($request);
            $user->profile_pic = $profile_pic;
        }
        if ($request->has('name'))
            $user->name = $request->name;
        if ($request->has('username'))
            $user->username = $request->username;
        $user->latitude = $latitude;
        $user->longitude = $longitude;
        //$user->gender = $request->gender;
        if ($request->has('country_id'))
            $user->country_id = $request->country_id;
        if ($request->has('city_id'))
            $user->city_id = $request->city_id;
        $user->save();
        $this->updateUserSettings($request);
        return $this->getFullUserData(auth()->id());
    }

    public function changePassword(Request $request)
    {
        $data = $this::find(auth()->id());
        $data->password = $request->get('new_password');
        $data->save();
        $set = compact('data');
        $set['message'] = 'Password changed successfully!';
        return $set;
    }

    public function getFullUserData($id)
    {
        $data = $this->with('city', 'country', 'settings', 'league')->find($id);
        $data->{'user_encrypted_id'} = $this->customEncryptToNumbers("$data->id", 12345);
        return $data;
    }

    private function uploadProfilePic(Request $request)
    {
        $currentPic = auth()->user()->profile_pic;
        if ($currentPic)
            Storage::delete($currentPic);
        $file = $request->file('profile_pic');
        $fileName = auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $uploaded = Storage::putFileAs('profile-pictures', $file, $fileName);
        return $uploaded;
    }

    // public function getHighestScoreAttribute()
    // {
    //     return $this->gameSubmissions->max('score');
    // }

    public function getLeaderboardByGame($id, $request)
    {
        $limit = $request->limit ?? 10;

        $users = $this->select('users.*')
            ->join('game_submissions', 'users.id', '=', 'game_submissions.user_id')
            ->where('game_submissions.game_id', $id)
            ->groupBy('users.id')
            ->selectRaw('MAX(game_submissions.score) as highest_score')
            ->orderBy('highest_score', 'desc');

        $usersPaginate = $users->paginate($limit)->toArray();

        $paginate['data'] = $usersPaginate['data'];
        unset($usersPaginate['data']);
        $paginate['page'] = $usersPaginate;
        $paginate['user'] = findCurrentPositionOfUserLeaderboard($users->get(), auth()->user());
        return $paginate;
    }

    public function getUserLeaderboardByGame($id)
    {
        return $this->with([
            'league',
            'gameSubmissions' => function ($query) use ($id) {
                $query->where('game_id', $id)
                    ->select('user_id', DB::raw('MAX(score) as highest_score'))
                    ->groupBy('user_id');
            }
        ])->whereHas('gameSubmissions', function ($query) use ($id) {
            $query->where('game_id', $id);
        })->withCount([
                    'gameSubmissions as highest_score' => function ($query) {
                        $query->select(DB::raw('MAX(score)'));
                    }
                ])->orderBy('highest_score', 'desc')->get();
    }

    public function biometric_login(Request $request)
    {
        $device_token = $request->get('device_token');
        $payload = $request->get('payload');
        $biometric = UserBiometric::where('device_id', $request->get('device_id'))->first();
        $public_key = $biometric->public_key;
        $pub_key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($public_key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $publicKeyResource = openssl_pkey_get_public($pub_key);
        if (!$publicKeyResource)
            throw new \Exception('invalid pub key');
        $signature = $request->get('signature');
        if (openssl_verify($payload, base64_decode($signature), $publicKeyResource, OPENSSL_ALGO_SHA256)) {
            $user = json_decode($payload);
            $user_id = $user->userId;
            $getUser = $this->find($user_id);
            if ($getUser) {
                $getUser->device_token = $device_token;
                $getUser->save();
                $token = $getUser->createToken('Auth')->plainTextToken;
                $getUser->{'token'} = $token;
                $data = $getUser;
                return $data;
            } else {
                throw new \Exception('invalid payload');
            }
        } else {
            throw new \Exception('invalid signature');
        }
    }

    private function updateUserSettings(Request $request)
    {
        $settings = [
            'user_id' => auth()->id()
        ];
        if ($request->has('event_creation_time')) {
            $settings['event_creation_time'] = $request->get('event_creation_time');
        }
        if ($request->has('event_end_time')) {
            $settings['event_end_time'] = $request->get('event_end_time');
        }
        // if($request->has('all_notification')){
        //     $settings['all_notification'] = $request->get('all_notification');
        // }
        if (isset($settings['event_creation_time']) || isset($settings['event_end_time'])) {
            UserSetting::UpdateOrCreate(['user_id' => auth()->id()], $settings);
        }
    }

    public function getCurrentMonthRegisteredUsers()
    {
        return $this::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
    }

    public static function byRadius($latitude,$longitude,$radius = 1)
    {
        $haversine = "(6371 * acos(cos(radians($latitude)) 
        * cos(radians(latitude)) 
        * cos(radians(longitude) - radians($longitude)) 
        + sin(radians($latitude)) 
        * sin(radians(latitude))))";

        return self::select('*')
            ->selectRaw("{$haversine} AS distance")
            ->having('distance', '<=', $radius)
            ->orderBy('distance')->get();
    }
}
