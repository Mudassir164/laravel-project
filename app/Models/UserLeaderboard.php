<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use function App\Helpers\addTimes;
use function App\Helpers\findCurrentPositionOfUserLeaderboard;

class UserLeaderboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAllLeaderboard($request)
    {
        $rank = $this->getUserPosition(auth()->id());
        $limit                  =   $request->limit ?? 10;
        $query                  =   $this->with(['user'])->orderBy('score', 'DESC');
        $data                   =   $query->paginate($limit)->toArray();
        $set['data']            =   $data['data'];
        unset($data['data']);
        $set['page']            =   $data;
        $set['user']            =   auth()->user();
        $set['user']['current_position'] = $rank['position'];
        $set['user']['score'] = $rank['score'];
        return $set;
    }

    public function leaderboadCreateUpdate($data = [])
    {
        $leaderboard = $this::where('user_id', $data['user_id'])->first();

        if ($leaderboard) {
            $leaderboard->score += $data['score'];
            $leaderboard->total_time = addTimes($leaderboard->total_time, $data['total_time']);
            $leaderboard->save();
        } else {
            $leaderboard = $this::create(
                [
                    'user_id' => $data['user_id'],
                    'score' => $data['score'],
                    'total_time' => $data['total_time'],
                ]
            );
        }
        return $leaderboard;
    }

    public function getUserPosition($userId)
    {
        $result = $this::select('user_id', 'score',)
            ->selectRaw('(SELECT COUNT(*) + 1 
                      FROM user_leaderboards AS ul2 
                      WHERE ul2.score > user_leaderboards.score) AS position',)
            ->from('user_leaderboards')
            ->where('user_id', $userId)
            ->orderByDesc('score')
            ->first();
        return [
            'position' => $result->position ?? 0,
            'score' => (int) round($result->score) ?? 0,
        ];
    }
}
