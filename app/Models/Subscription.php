<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function webUser()
    {
        return $this->belongsTo(WebUser::class);
    }

    public function subscriptionItem()
    {
        return $this->hasOne(SubscriptionItem::class);
    }

    public function getAllUserSubscription($request)
    {
        $limit = $request->limit ?? 10;
        return $this->with(['webUser'])->paginate($limit);
    }
}
