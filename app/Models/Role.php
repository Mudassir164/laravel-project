<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model,SoftDeletes};

class Role extends Model
{
    use HasFactory,SoftDeletes;

    public const ADMIN = 1;
    public const PUB_OWNER = 2;
    public const SPONSOR = 3;
    public const ORGANIZER = 4;

    public function webRoutes()
    {
        return $this->belongsToMany(WebRoute::class, RoleWebRoute::class);
    }
}
