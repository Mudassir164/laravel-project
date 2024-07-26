<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAllFeedback($where = [], $filters = [])
    {
        return $this->with(['game', 'user'])->get();
    }

    public function storeGameFeedback($request)
    {
        return $this::create($request->only($this->getFillable()));
    }
}
