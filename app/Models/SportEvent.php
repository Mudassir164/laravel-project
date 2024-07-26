<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Svg\Tag\Rect;

class SportEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['sport_event_id', 'sport_event_obj'];

    protected $appends = ['start_time', 'remaining_time','event_name'];

    protected $casts = [
        'sport_event_obj' => 'json',
    ];


    public function StartTime(): Attribute
    {
        $property = $this->sport_event_obj['sport_event']['start_time'] ?? null;
        if ($property) {
            $startTime = Carbon::parse($property);
            $unixTimestamp = $startTime->timestamp;
        }
        return Attribute::get(fn () => $property ? $unixTimestamp : null);
    }

    public function RemainingTime(): Attribute
    {
        $remaining = null;
        $property = $this->sport_event_obj['sport_event']['start_time'] ?? null;
        if ($property) {
            $start_time = Carbon::parse($property);
            $now = now();
            if ($start_time > $now) {
                $remaining = $start_time->diffInSeconds($now);
            }
        }
        return Attribute::get(fn () => ($remaining && $remaining > 0) ? $remaining : null);
    }

    public function EventName() : Attribute
    {
        $name = $this->sport_event_obj['sport_event']['sport_event_context']['competition']['name'] ?? null;
        return Attribute::get(fn() => $name);
    }
    public function events()
    {
        return $this->hasMany(Game::class,'sport_event_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'sport_event_id');
    }

    public function list(Request $request)
    {
        try {
            $haversine = false;
            $search = $request->get('search') ?? false;
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            $radius = 1; // You might want to make this configurable
            $limit = $request->get('limit') ?? 5;
            $now = now();
            $schedule = $request->get('schedule') ?? 'today_upcoming';
            $startDate = $request->start_date ? date('Y-m-d', strtotime($request->start_date)) : false;
            $endDate = $request->end_date ? date('Y-m-d', strtotime($request->end_date)) : false;
            $dateRange = $startDate && $endDate;

            $query = SportEvent::query(); // Ensure you are querying the SportEvent model

            switch ($schedule) {
                case 'today':
                    $query = $query->whereRaw("DATE(JSON_UNQUOTE(JSON_EXTRACT(sport_event_obj, '$.sport_event.start_time'))) = ?", [$now->toDateString()]);
                    break;
                case 'upcoming':
                    $query = $query->whereDate('sport_event_obj->sport_event->start_time', '>', $now);
                    break;
                case 'completed':
                    $query = $query->whereDate('sport_event_obj->sport_event->start_time', '<', $now);
                    break;
                case 'today_upcoming':
                    $query = $query->whereRaw("DATE(JSON_UNQUOTE(JSON_EXTRACT(sport_event_obj, '$.sport_event.start_time'))) = ?", [$now->toDateString()])->orWhereDate('sport_event_obj->sport_event->start_time', '>', $now);
                    break;
            }

            if ($dateRange) {
                $query = $query->whereBetween('sport_event_obj->sport_event->start_time', [$startDate, $endDate]);
            }

            if ($longitude && $latitude) {
                $haversine = true;
            }

            if ($request->pub_owner_id) {
                $query = $query->whereHas('games', function ($q) use ($request) {
                    $q->where('web_user_id', $request->pub_owner_id);
                });
            }

            if ($haversine) {
                $query = $query->whereHas('games.user', function ($q) use ($latitude, $longitude, $radius) {
                    $q->selectRaw(
                        '(3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', 
                        [$latitude, $longitude, $latitude]
                    )->havingRaw("distance < ?", [$radius]);
                });
            }

            if ($search) {
                $query = $query->whereRaw("LOWER(JSON_EXTRACT(sport_event_obj, '$.sport_event.competitors[*].name')) LIKE LOWER(?)", ["%$search%"]);
            }

            // dd($query->toSql());
            $query = $query->withCount('games')->paginate($limit);

            return $query->toArray();
        } catch (\Exception $e) {
            return $this->responseToClient(['message' => $e->getMessage()], 400);
        }
    }

    public function getEvents(Request $request)
    {
        $request->merge(['schedule' => 'today']);
        $today = $this->list($request)['data'] ?? [];

        $request->merge(['schedule' => 'upcoming']);
        $upcoming = $this->list($request)['data'] ?? [];

        return compact('today', 'upcoming');
    }


}
