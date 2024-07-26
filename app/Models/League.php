<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\{Storage};

class League extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getAllLeagues($where = [])
    {
        return $this->where($where)->get();
    }

    public function getImageAttribute($value)
    {
        $uri = null;
        if($value) $uri = Storage::url($value);
        return $uri;
        // return Attribute::get(fn() => $this->image ? Storage::url($this->image) : null);
    }
}
