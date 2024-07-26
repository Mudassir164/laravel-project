<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Sponsorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'web_user_id',
        'game_id',
        'image',
        'status',
        'expiry_date',
        'title',
        'sponsor_name',
        'game_type_id',
        'company_link',
        'amount',
        'message',
    ];

    protected $appends = [
        'sponsorship_image_url'
    ];

    public function sponsorshipImageUrl(): Attribute
    {
        return Attribute::get(fn() => $this->image ? Storage::url($this->image) : null);
    }

    public function addSponsorship(array $details, $startDate, $sponsorId)
    {
        if (isset($details['image'])) {
            $details['image'] = $this->uploadProfilePic($details['image']);
        }
        $details['expiry_date'] = $startDate;
        $details['web_user_id'] = $sponsorId;
        return $this->create($details);
    }

    public function updateSponsorship(string $id, array $details)
    {
        if (isset($details['image'])) {
            $details['image'] = $this->uploadProfilePic($details['image']);
        }
        $sponsorship = $this->find($id);
        if ($sponsorship) {
            $sponsorship->update($details);
            return $sponsorship;
        }
        return null;
    }

    public function sponsorshipStatusUpdate($id, $status)
    {
        $sponsorship = $this->find($id);
        if ($sponsorship) {
            $sponsorship->status = $status;
            $sponsorship->update();
            return $sponsorship;
        }
        return null;
    }

    private function uploadProfilePic($file)
    {
        $currentPic = auth()->user()->image;
        if ($currentPic) {
            Storage::delete($currentPic);
        }
        $fileName = auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $uploaded = Storage::putFileAs('web-sponsorship-images', $file, $fileName);
        return $uploaded;
    }

    public function getAllSponsorship($where = [])
    {
        return $this->where($where);
    }

    // public function processJobs($gameId){
    //     $route = Request()->route()->uri();
    // }

}

