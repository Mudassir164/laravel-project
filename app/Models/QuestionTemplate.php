<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTemplate extends Model
{
    use HasFactory;


    public function getAllQuestions($where = [])
    {
        return $this->where($where)->get();
    }
}
