<?php

namespace App\Helpers;

use App\Jobs\CouponGameResultJob;
use App\Jobs\GameResultJob;
use App\Models\GameSubmission;
use App\Models\GameType;
use App\Models\League;
use App\Models\User;
use App\Models\WebUser;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Storage;

if (!function_exists('addTimeDurations')) {
    function addTimeDurations(array $durations)
    {
        $totalMinutes = 0;

        foreach ($durations as $duration) {
            list($hours, $minutes) = explode(':', $duration);

            $totalMinutes += $hours * 60 + $minutes;
        }

        $finalHours = floor($totalMinutes / 60);
        $finalMinutes = $totalMinutes % 60;

        return sprintf("%02d:%02d", $finalHours, $finalMinutes);
    }
}

function addTimes($time1, $time2)
{
    $time1Parts = explode(':', $time1);
    $time2Parts = explode(':', $time2);

    $hours = $time1Parts[0] + $time2Parts[0];
    $minutes = $time1Parts[1] + $time2Parts[1];
    $seconds = (isset($time1Parts[2]) ? $time1Parts[2] : 0) + (isset($time2Parts[2]) ? $time2Parts[2] : 0);

    if ($seconds >= 60) {
        $minutes += floor($seconds / 60);
        $seconds = $seconds % 60;
    }

    if ($minutes >= 60) {
        $hours += floor($minutes / 60);
        $minutes = $minutes % 60;
    }

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

function filterSeasonsScheduleDataBySearch($search, $schedules)
{
    $date = now();
    if ($search == 'ongoing') {
        $schedules = $schedules->filter(function ($item) use ($date) {
            return strpos(object_get($item, 'sport_event.start_time'), $date) !== false;
        })->values();
    } else if ($search == 'upcoming') {
        $schedules = $schedules->filter(function ($item) use ($date) {
            return Carbon::parse(object_get($item, 'sport_event.start_time')) > $date;
        })->values();
    } else if ($search == 'completed') {
        $schedules = $schedules->filter(function ($item) use ($date) {
            return Carbon::parse(object_get($item, 'sport_event.start_time')) < $date;
        })->values();
    }

    return $schedules;
}

function findCurrentPositionOfUserLeaderboard($users = [], $existingUser)
{
    $currentUserPosition = 0;
    $currentUser = null;

    foreach ($users as $index => $user) {
        $currentUserPosition = $index + 1;
        if ($user->id == $existingUser->id) {
            $currentUser = $user;
            $currentUser->current_position = $currentUserPosition;
            break;
        }
    }
    return $currentUser;
}

function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function dispatchQueueForResult($game, $event)
{
    if ($game->type && $game->type->slug == 'coupon') {
        $date = Carbon::createFromTimestamp($event->start_time);
        $dispatchDate = $date->addMinutes(160);
        CouponGameResultJob::dispatch($game->id, $dispatchDate)->delay($dispatchDate);
    } else if ($game->type && $game->type->slug == 'quiz') {
        $date = Carbon::createFromTimestamp($event->start_time);
        $dispatchDate = $date->addMinutes(100);
        GameResultJob::dispatch($game->id)->delay($dispatchDate);
    }
}

function checkGameSubmission($game)
{
    if ($game->type && $game->type->slug == 'coupon') {
        $startTime = Carbon::parse($game->sport_event->start_time);
        $currentTime = now();
        if ($startTime < $currentTime) {
            throw new Exception("Match is started, you can't submit coupon now!");
        }
    } else if ($game->type && $game->type->slug == 'quiz') {
        $startTime = Carbon::parse($game->sport_event->start_time);
        $endTime = Carbon::parse($game->sport_event->start_time)->addMinutes(100);
        $currentTime = now();

        if ($startTime > $currentTime) {
            throw new Exception("Match is not started yet!");
        } else if ($currentTime > $endTime) {
            throw new Exception("Match is finished, You are not able to submit it now!");
        }
    }
}

function checkAlreadyGameSubmission($game, $user)
{
    $submission = GameSubmission::where(['game_id' => $game->id, 'user_id' => $user->id])->first();
    if ($submission) {
        throw new Exception("You have already played this game!");
    }
}

function updateUserLeague($userLeaderboard)
{
    $league = League::where("score", '<=', $userLeaderboard->score)->orderBy('score', 'DESC')->first();
    if ($league) {
        User::updateUser(
            $userLeaderboard->user_id,
            [
                'league_id' => $league->id
            ]
        );
    }
}

function getAllWebUserIds()
{
    if (auth()->user()->role && auth()->user()->role->slug == 'pub_owner') {
        $webRoleIds = WebUser::where('parent_id', auth()->user()->id)->pluck('id')->toArray();
        array_push($webRoleIds, auth()->user()->id);
    } else if (auth()->user()->role && auth()->user()->role->slug == 'organizer') {
        $webRoleIds = WebUser::where('parent_id', auth()->user()->parent_id)->pluck('id')->toArray();
        array_push($webRoleIds, auth()->user()->parent_id);
    }
    return $webRoleIds;
}

function getStartTimeForGame($typeId, $time)
{
    $startTime = null;
    $gameType = GameType::where('id', $typeId)->first();
    if ($gameType->slug == 'quiz') {
        $startTime = date('H:i', strtotime($time));
    } else if ($gameType->slug == 'coupon') {
        $startTime = Carbon::parse($time);
        $startTime = $startTime->subMinutes(100)->format('H:i');
    }
    return $startTime;
}

function uploadImage($file, $dir)
{
    $fileName = rand('00000', '99999') . '_' . time() . '.' . $file->getClientOriginalExtension();
    $uploaded = Storage::putFileAs($dir, $file, $fileName);
    return $uploaded;
}




















