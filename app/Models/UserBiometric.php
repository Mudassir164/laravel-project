<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBiometric extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'public_key'];

    public function storeAndUpdateUserBiometric($request)
    {
        $data = $request->only($this->getFillable());
        return $this->UpdateOrCreate(
            [
                'device_id' => $request->device_id
            ],
            $data
        );
    }
}
