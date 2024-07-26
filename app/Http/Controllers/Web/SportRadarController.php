<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Libraries\SportsRadar;
use Illuminate\Http\Request;

use function App\Helpers\filterSeasonsScheduleDataBySearch;

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
            // Fetch seasons data from SportsRadar
            $response = $this->sports_radar->seasons($request);
            $responseData = json_decode($response);
            
            // Filter seasons
            $seasons = collect(object_get($responseData, 'seasons'));
            
            // Define the initial words you want to match
            $keywords = ['Premier League', 'Eliteserien'];
            
            $filteredSeasons = $seasons->filter(function ($item) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (strpos($item->name, $keyword) === 0) {
                        return true;
                    }
                }
                return false;
            });
            $set['seasons'] = $filteredSeasons->sortByDesc(function ($item) {
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
            $search     = $request->get('search') ?? false;
            $response = $this->sports_radar->seasonSchedule($seasonID,$request);
            $responseData = json_decode($response);
            $schedules = collect(object_get($responseData, 'schedules'));
            if($search){
                $schedules = filterSeasonsScheduleDataBySearch($search, $schedules);
            } 
            $set['schedules'] = $schedules;
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
}
