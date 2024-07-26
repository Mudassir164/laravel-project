<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event_creation_time', 'event_end_time', 'all_notification'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUserSetting($where = [])
    {
        return $this->with(['user'])->where($where)->first();
    }

    public static function storeAndUpdateUserSetting($where = [], $request)
    {
        $data = $request->only(self::getFillable());
        return self::UpdateOrCreate($where, $data);
    }
}
