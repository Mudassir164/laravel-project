<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'attachment', 'status', 'web_user_id'];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = [
        'attachment_url',
    ];

    public function attachmentUrl(): Attribute
    {
        return Attribute::get(fn() => $this->attachment ? Storage::url($this->attachment) : null);
    }

    public function getAllRewards($where, $request)
    {
        $limit = $request->limit ?? 10;
        return $this->where($where)
            ->when(!empty($request->search), function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%');
            })->paginate($limit);
    }

    public function getAllActiveRewards($where = [], $filters = [])
    {
        return $this->where($where)->where('status', 1)
            ->when(count($filters) > 0, function ($q) use ($filters) {
                $q->when(isset($filters['web_user_ids']) && count($filters['web_user_ids']) > 0, function ($q) use ($filters) {
                    $q->whereIn('web_user_id', $filters['web_user_ids']);
                });
            })->get();
    }

    public function storeReward($data)
    {
        return $this->create($data);
    }

    public function findReward($id)
    {
        return $this->find($id);
    }

    public function updateReward($id, $data)
    {
        return $this->find($id)->update($data);
    }
}
