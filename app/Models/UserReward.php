<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReward extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_redeem' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function getAllRewards($where = [], $request = null)
    {
        $query = $this->with(["reward", "position", "user", 'game'])->where($where);
        if ($request) {
            $limit = $request->limit ?? 10;
            $query = $query->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($query) use ($request) {
                    $query->where('email', 'LIKE', '%' . $request->search . '%');
                })->orWhereHas('position', function($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->search . '%');
                })->orWhereHas('game', function($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->search . '%');
                })->orWhereHas('reward', function($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->search . '%');
                });
            })->paginate($limit);
        } else {
            $query = $query->get();
        }
        return $query;
    }

    public function findUserReward($id)
    {
        return $this->with(["reward", "position", "user", 'game'])->find($id);
    }

    public function updateUserReward($id, $data = [])
    {
        return $this->find($id)->update($data);
    }
}
