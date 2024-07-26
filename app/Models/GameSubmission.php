<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSubmission extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function game()
    {
        return $this->belongsTo(Game::class)->with(['type', 'user'])->withCount(['questions']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameSubmissionDetails()
    {
        return $this->hasMany(GameSubmissionDetail::class)->with([
            'gameQuestion:id,game_question_type_id,game_question_difficulty_id,description',
            'gameQuestionOption:id,option,correct_option',
            'GameQuestionDifficulty'
        ]);
    }

    public function correct_answers()
    {
        return $this->hasMany(GameSubmissionDetail::class)->where('correct_answer', 1);
    }

    public function attempted_questions()
    {
        return $this->hasMany(GameSubmissionDetail::class)->where('game_question_option_id', '!=', null);
    }

    public function createGameSubmission($data = [])
    {
        return $this->create($data);
    }

    public function findFullGameSubmission($id)
    {
        return $this
            //->with(['game:id,name','gameSubmissionDetails'])
            ->withCount('correct_answers')
            ->find($id);
    }

    public function getGameSubmissions($where = [], $filters = [])
    {
        return $this->with(['game', 'game.sport_event'])->withCount(['correct_answers', 'attempted_questions'])->where($where)
            ->when(count($filters) > 0, function ($q) use ($filters) {
                $q->when(!empty($filters['game_id']), function ($q) use ($filters) {
                    $q->whereHas('game', function ($q) use ($filters) {
                        $q->where('id', $filters['game_id']);
                    });
                })->when(!empty($filters['search']), function ($query) use ($filters) {
                    $query->whereHas('game', function ($q) use ($filters) {
                        $q->where('name', 'LIKE', '%' . $filters['search'] . '%');
                    });
                });
            });
    }
}
