<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class GameQuestionOption extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'game_question_id',
        'option',
        'correct_option',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function gameQuestion() 
    {
        return $this->belongsTo(GameQuestion::class,'game_question_id');
    }
}
