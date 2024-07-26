<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class GameType extends Model
{
    use HasFactory,SoftDeletes;

    public function getGameTypes()
    {
        return $this->all();
    }
}
