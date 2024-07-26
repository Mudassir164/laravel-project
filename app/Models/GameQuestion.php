<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class GameQuestion extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'game_id',
        'game_question_type_id',
        'game_question_difficulty_id',
        'attachment',
        'description',
        'duration',
    ];

    protected $appends = ['points','type', 'correct_answers', 'wrong_answers', 'un_answered', 'attachment_url'];

    public function options()
    {
        return $this->hasMany(GameQuestionOption::class,'game_question_id');
    }

    public function gameSubmissionDetails()
    {
        return $this->hasMany(GameSubmissionDetail::class,'game_question_id');
    }

    public function Type(): Attribute
    {
        $type = GameQuestionType::find($this->game_question_type_id);
        return Attribute::get(fn () => $type->slug ?? null);
    }

    public function Points(): Attribute
    {
        $difficulty = GameQuestionDifficulty::find($this->game_question_difficulty_id);
        return Attribute::get(fn () => $difficulty->points ?? null);
    }

    public function CorrectAnswers(): Attribute
    {
        $correctAnswer = $this->gameSubmissionDetails->where('correct_answer', 1)->count();
        return Attribute::get(fn () => $correctAnswer ?? 0);
    }

    public function WrongAnswers(): Attribute
    {
        $correctAnswer = $this->gameSubmissionDetails->where('correct_answer', 0)->count();
        return Attribute::get(fn () => $correctAnswer ?? 0);
    }

    public function UnAnswered(): Attribute
    {
        $unAnswered = $this->gameSubmissionDetails->where('game_question_option_id', null)->count();
        return Attribute::get(fn () => $unAnswered ?? 0);
    }

    public function AttachmentUrl(): Attribute
    {
        return Attribute::get(fn() => $this->attachment ? Storage::url($this->attachment) : null);
    }

    public function getAllGameQuestion($where = [], $filters = [])
    {
        return $this->with(['options' => function ($q) use ($filters) {
            $q->where('correct_option', 1);
        }])->where($where);
    }
}
