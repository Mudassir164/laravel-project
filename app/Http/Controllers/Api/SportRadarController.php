<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\SportsRadar;
use Illuminate\Http\Request;

class SportRadarController extends Controller
{
    private $sports_radar;

    public function __construct()
    {
        $this->sports_radar = new SportsRadar();
    }

    public function getSeasons(Request $request)
    {
        try {
            $response = $this->sports_radar->seasons($request);
            $responseData = json_decode($response);
            $seasons = collect(object_get($responseData, 'seasons'));
            $set['seasons'] = $seasons->sortByDesc(function ($item) {
                return strtotime($item->start_date);
            })->values();
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function getSeasonSchedule($seasonID,Request $request)
    {
        try {
            $set = [];
            $start_time = $request->get('start_time') ?? false;
            $response = $this->sports_radar->seasonSchedule($seasonID,$request);
            $responseData = json_decode($response);
            $schedules = collect(object_get($responseData, 'schedules'));
            if($start_time){
                $schedules = $schedules->filter(function ($item) use ($start_time) {
                    return strpos(object_get($item,'sport_event.start_time'), $start_time) !== false;
                })->values();
            }
            $set['schedules'] = $schedules;
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
