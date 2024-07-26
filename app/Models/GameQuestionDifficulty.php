<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameQuestionDifficulty extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'points',
    ];

    public function addGameQuestionDifficulty(Request $request){
        $name = $request->name;
        $slug = Str::slug($name, '_');
        $request->merge(['slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
        $data = $request->all();
        $user = $this->create($data);
        return $user;
    }

    public function updateGameQuestionDifficulty(Request $request){
        $name = $request->name;
        $slug = Str::slug($name, '_');
        $request->merge(['slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
        $data = $request->all();
        $user = $this->create($data);
        return $user;
    }
}
