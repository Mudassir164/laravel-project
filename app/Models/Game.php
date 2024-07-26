<?php

namespace App\Models;

use App\Jobs\{GameCreatedPushNotificationJob, GameEndPushNotificationJob, GameEndPushNotificationJobForWeb, GameStartPushNotificationJob};
use App\Libraries\SportsRadar;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Http\Request;
use function App\Helpers\addTimeDurations;
use function App\Helpers\dispatchQueueForResult;
use function App\Helpers\getStartTimeForGame;
use function App\Helpers\uploadImage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sport_event_id',
        'web_user_id',
        'game_type_id',
        'game_category_id',
        'limit',
        'start_date',
        'start_time',
        'rules',
    ];

    protected $appends = [
        'game_remaining_time',
        'match_remaining_time',
        'has_feedback',
        'start_date_time'
    ];

    protected $casts = [
        'sport_event_id' => 'integer'
    ];

    public function organizer()
    {
        return $this->belongsTo(WebUser::class, 'web_user_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(GameQuestion::class, 'game_id')->with(['options']);
    }

    public function user()
    {
        return $this->belongsTo(WebUser::class, 'web_user_id');
    }

    public function appUser()
    {
        return $this->belongsToMany(User::class, 'game_submissions');
    }

    public function type()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }

    public function sport_event()
    {
        return $this->belongsTo(SportEvent::class, 'sport_event_id');
    }

    public function gameSubmissions()
    {
        return $this->hasMany(GameSubmission::class)->with('gameSubmissionDetails', 'correct_answers');
    }

    public function feedbacks()
    {
        return $this->hasMany(GameFeedback::class);
    }

    public function rewards()
    {
        return $this->belongsToMany(Reward::class, 'game_rewards')
            ->withPivot('position_id')
            ->withTimestamps();
    }

    public function GameRemainingTime(): Attribute
    {
        $remaining = null;
        $start_date = $this->start_date;
        $start_time = $this->start_time;
        $property = Carbon::parse($start_date . ' ' . $start_time);
        if ($property) {
            $now = now();
            if ($property > $now) {
                $remaining = $property->diffInSeconds($now);
            }
        }
        return Attribute::get(fn() => ($remaining && $remaining > 0) ? $remaining : null);
    }

    public function MatchRemainingTime(): Attribute
    {
        $event = SportEvent::find($this->sport_event_id);
        return Attribute::get(fn() => $event->remaining_time ?? null);
    }

    public function StartDateTime(): Attribute
    {
        $dateTime = date('Y-m-d H:i:s', strtotime($this->start_date . ' ' . $this->start_time));
        return Attribute::get(fn() => $dateTime ?? null);
    }

    // Define the scopeUpcoming method
    // public function scopeUpcoming($query, $typeId)
    // {
    //     if ($typeId) {
    //         $date = date('Y-m-d');
    //         if ($typeId == 1) {
    //             $startTime = date('H:i');
    //             $endTime = Carbon::parse($this->start_time)->addMinutes(100)->format('H:i');
    //         } else if ($typeId == 2) {
    //             $startTime = Carbon::parse($this->start_time)->subMinutes(100)->format('H:i');
    //             $endTime = Carbon::parse($this->start_time)->format('H:i');
    //         }
    //         $dateTime = date('Y-m-d H:i:s', strtotime($date . ' ' . $startTime));
    //         $dateEndTime = date('Y-m-d H:i:s', strtotime($date . ' ' . $endTime));
    //         return $query->where('game_type_id', $typeId)
    //             ->where(function ($query) use ($dateTime, $dateEndTime) {
    //                 $query->whereRaw("CONCAT(start_date, ' ', start_time) > ?", [$dateTime])
    //                     ->orWhere(function ($query) use ($dateTime, $dateEndTime) {
    //                         $query->whereRaw("CONCAT(start_date, ' ', start_time) <= ?", [$dateTime])
    //                             ->whereRaw("CONCAT(start_date, ' ', start_time) + INTERVAL 100 MINUTE >= ?", [$dateEndTime]);
    //                     });
    //             });
    //         // ->where('start_date', '>', $date)->where('start_time', '>', $time);
    //     }
    //     return $query;
    // }

    public function getHasFeedbackAttribute()
    {
        $user = auth()->user();
        if ($user) {
            return $this->feedbacks()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    public function getAllGames()
    {
        return $this->with(['questions', 'questions.options', 'type', 'sport_event']);
    }

    public function gameStore(Request $request)
    {
        $set_options = [];
        $data = $request->all();
        $eventID = $data['sport_event_id'];
        $sport_event_obj = $this->getSportEventDetails($eventID);
        $data['start_date'] = date('Y-m-d', strtotime($sport_event_obj->sport_event->start_time));
        $data['start_time'] = getStartTimeForGame($request->game_type_id, $sport_event_obj->sport_event->start_time);
        $sport_event_set = ['sport_event_id' => $eventID, 'sport_event_obj' => $sport_event_obj];
        $event = SportEvent::UpdateOrCreate(['sport_event_id' => $eventID], $sport_event_set);
        $data['sport_event_id'] = $event->id;
        $questions = collect($request->get('questions'));
        unset($data['questions']);
        $durations = $questions->pluck('duration')->toArray();
        $data['limit'] = addTimeDurations($durations);
        $game = $this::create(collect($data)->only($this->getFillable())->toArray());
        $game_id = $game->id;
        $options = $questions->pluck('options')->toArray();
        $questions = $questions->transform(function ($item,$key) use ($game_id,$request) {
            $attachment = $request->file('questions')[$key]['attachment'] ?? null; 
            if (isset($attachment) && !empty($attachment)) {
                $image = uploadImage($attachment, 'game-question');
                $item['attachment'] = $image;
            }
            $item['game_id'] = $game_id;
            unset($item['options']);
            return $item;
        });
        GameQuestion::insert($questions->toArray());
        $questions_ids = GameQuestion::orderBy('id', 'desc')->limit($questions->count())->get()->pluck('id');
        $questions_ids_array = $questions_ids->toArray();
        sort($questions_ids_array);
        foreach ($options as $index => &$value) {
            $game_question_id = $questions_ids_array[$index];
            foreach ($value as &$v) {
                $v['game_question_id'] = $game_question_id;
                array_push($set_options, $v);
            }
        }
        GameQuestionOption::insert($set_options);
        // Prepare the sync data
        $syncData = [];
        if(isset($request->rewards)){
            foreach ($request->rewards as $reward) {
                $syncData[$reward['reward_id']] = ['position_id' => $reward['position_id']];
            }
            // Sync the rewards
            $game->rewards()->sync($syncData);
        }

        return array('data' => $this->getFullGame($game_id), 'event' => $event);
    }

    public function getFullGame($id)
    {
        return $this->with(['questions', 'questions.options', 'user', 'appUser', 'type', 'sport_event', 'gameSubmissions', 'feedbacks', 'rewards'])->find($id);
    }

    public static function getSportEventDetails($eventID)
    {
        $sportRadar = new SportsRadar;
        $eventObj = $sportRadar->eventLineUp($eventID);
        return json_decode($eventObj);
    }

    public function list($eventID, Request $request)
    {
        $limit = $request->get('limit') ?? 5;
        $type = $request->get('type_id') ?? false;


        $userId = auth()->id();

        $startDate = $request->get('start_date') ? date('Y-m-d', strtotime($request->get('start_date'))) : false;
        $endDate = $request->get('end_date') ? date('Y-m-d', strtotime($request->get('end_date'))) : false;
        $date = date('Y-m-d H:i:s');

        $set = ['data' => []];


        $data = $this::with(['user:id,pub_name', 'type:id,name'])->withCount('questions')
            ->where('sport_event_id', $eventID)
            ->whereDoesntHave('gameSubmissions', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->when(!empty($type), function ($query) use ($date, $type) {
                $query->where(function ($query) use ($date) {
                    $query->whereRaw("CONCAT(start_date, ' ', start_time) + INTERVAL 100 MINUTE >= ?", [$date]);
                    // if ($type == 1) {
                    //     $query->whereRaw("CONCAT(start_date, ' ', start_time) > ?", [$date]);
                    // } else if ($type == 2) {
                    //     $query->whereRaw("CONCAT(start_date, ' ', start_time) > ?", [$date]);
                    // }
                    // $query->whereRaw("CONCAT(start_date, ' ', start_time) > ?", [$dateTime])
                    //     ->orWhere(function ($query) use ($dateTime, $dateEndTime) {
                    //         $query->whereRaw("CONCAT(start_date, ' ', start_time) <= ?", [$dateTime])
                    //             ->whereRaw("CONCAT(start_date, ' ', start_time) + INTERVAL 100 MINUTE >= ?", [$dateEndTime]);
                    //     });
                })->where('game_type_id', $type);
            })->when(!empty($startDate) && !empty($endDate), function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '>=', $startDate)
                    ->where('start_date', '<=', $endDate);
            });


        $data = $data->paginate($limit)->toArray();

        if (count($data['data'])) {
            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
        }

        return $set;
    }

    public function onGoingGames($gameIds = [])
    {
        return $this->with(['appUser'])->where('start_date', date('Y-m-d'))->where('start_time', '>', date('H:i:s'))
            ->when(count($gameIds) > 0, function ($q) use ($gameIds) {
                $q->whereIn('id', $gameIds);
            })->get();
    }

    public function upcomingGames($gameIds = [])
    {
        $currentDateTime = now(); // Get the current date and time

        return $this->with(['appUser'])
            ->whereRaw("CONCAT(start_date, ' ', start_time) > ?", [$currentDateTime])
            ->when(count($gameIds) > 0, function ($q) use ($gameIds) {
                $q->whereIn('id', $gameIds);
            })
            ->get();
    }

    public function getUserGameList($where = [], $filters = [])
    {
        return $this->with(['gameSubmissions', 'questions'])->where($where)
            ->when(count($filters) > 0, function ($query) use ($filters) {
                $query->when(!empty($filters['user_id']), function ($query) use ($filters) {
                    $query->whereHas('gameSubmissions', function ($query) use ($filters) {
                        $query->where('user_id', $filters['user_id']);
                    });
                })->when(!empty($filters['sport_event_id']), function ($query) use ($filters) {
                    $query->whereHas('sport_event', function ($query) use ($filters) {
                        $query->where('sport_event_id', $filters['sport_event_id']);
                    });
                })->when((!empty($filters['game_ids']) && count($filters['game_ids']) > 0), function ($query) use ($filters) {
                    $query->whereIn('id', $filters['game_ids']);
                })->when(isset($filters['web_user_ids']) && count($filters['web_user_ids']) > 0, function ($q) use ($filters) {
                    $q->whereIn('web_user_id', $filters['web_user_ids']);
                });
            });
    }

    public function processJobs($game, $event)
    {
        $route = Request()->route()->uri();

        $limit = $game->limit;
        // $limit = '00:01:00';

        $startTime = $game->start_time;
        // $startTime = '17:51:00';

        $startDate = $game->start_date;
        // $startDate = '2024-07-15';

        // Parse the time intervals
        $limitParts = explode(':', $limit);
        $startParts = explode(':', $startTime);

        $limitInterval = CarbonInterval::hours($limitParts[0])->minutes($limitParts[1])->seconds($limitParts[2]);
        $startInterval = CarbonInterval::hours($startParts[0])->minutes($startParts[1])->seconds($startParts[2]);

        // Add the intervals
        $totalInterval = $limitInterval->add($startInterval);

        // Game End Time
        $gameEndTime = Carbon::parse("$startDate $totalInterval");

        // GameEndPushNotificationJob (before 15 mints of game end)
        $gameEndNotificationTime = $gameEndTime->copy()->subMinutes(15);
        $gameEndDelay = $gameEndNotificationTime->diffInSeconds(Carbon::now());
        GameEndPushNotificationJob::dispatch(auth()->id(), $game, $route)->delay(now()->addSeconds($gameEndDelay));

        // GameEndPushNotificationJobForWeb (after game end)
        $gameEndTimeDelay = $gameEndTime->diffInSeconds(Carbon::now());
        GameEndPushNotificationJobForWeb::dispatch(auth()->id(), $game, $route)->delay(now()->addSeconds($gameEndTimeDelay));

        // GameStartPushNotificationJob
        $gameStartTime = Carbon::parse("$startDate $startTime");
        $gameStartNotificationTime = $gameStartTime->copy()->subMinutes(30);
        $gameStartDelay = $gameStartNotificationTime->diffInSeconds(Carbon::now());
        GameStartPushNotificationJob::dispatch(auth()->id(), $game, $route)->delay(now()->addSeconds($gameStartDelay));

        // GameCreatedPushNotificationJob
        GameCreatedPushNotificationJob::dispatch(auth()->id(), $game, $route);

        // Dispatch Queue Jobs For Results
        dispatchQueueForResult($game, $event);
    }
}