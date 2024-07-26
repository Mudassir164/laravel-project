<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\GetGraphsRequest;
use App\Models\GameSubmission;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemographicsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function getChart($type, GetGraphsRequest $request)
    {
        try {
            switch ($type) {
                case 'getGendorGraph':
                    $data = $this->getGendorGraph($request);
                    break;
                case 'getGamesPlayedGraph':
                    $data = $this->getGamesPlayedGraph($request);
                    break;
                case 'getCountriesGraph':
                    $data = $this->getCountriesGraph($request);
                    break;
                default:
                    throw new \Exception('default Case triggerd');
                    break;
            }
            $response = $data ?? [];
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    private function getGendorGraph(Request $request)
    {
        $duration = $request->get('duration');
        $query = $this->user;

        switch ($duration) {
            case 'weekly':
                // Generate date references for the last 7 days
                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT CURDATE() - INTERVAL seq DAY AS date
                    FROM (
                        SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
                    ) AS seq_days
                ) AS date_ref'));

                // Join with user data and aggregate
                $query = $query->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                    $join->on(DB::raw('DATE(users.created_at)'), '=', 'date_ref.date');
                })
                    ->select(
                        'date_ref.date AS label',
                        DB::raw('COALESCE(SUM(CASE WHEN gender = "male" THEN 1 ELSE 0 END), 0) AS male'),
                        DB::raw('COALESCE(SUM(CASE WHEN gender = "female" THEN 1 ELSE 0 END), 0) AS female')
                    )
                    ->groupBy('date_ref.date')
                    ->orderBy('date_ref.date', 'DESC');
                break;

            case 'monthly':
                $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
                $lastDayOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT DATE_FORMAT(DATE_ADD("' . $firstDayOfMonth . '", INTERVAL seq DAY), "%Y-%m-%d") AS date_label
                    FROM (
                        SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31
                    ) AS seq_days
                    WHERE DATE_ADD("' . $firstDayOfMonth . '", INTERVAL seq DAY) <= "' . $lastDayOfMonth . '"
                ) AS date_ref'));

                // Join with user data and aggregate
                $query = $query->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                    $join->on(DB::raw('DATE(users.created_at)'), '=', 'date_ref.date_label');
                })
                    ->select(
                        'date_ref.date_label AS label',
                        DB::raw('COALESCE(SUM(CASE WHEN users.gender = "male" THEN 1 ELSE 0 END), 0) AS male'),
                        DB::raw('COALESCE(SUM(CASE WHEN users.gender = "female" THEN 1 ELSE 0 END), 0) AS female')
                    )
                    ->groupBy('date_ref.date_label')
                    ->orderBy('date_ref.date_label', 'ASC');

                break;

            case 'yearly':
                // Generate year references for the last 5 years
                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT YEAR(NOW()) - seq AS year
                    FROM (
                        SELECT 0 AS seq #UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                    ) AS seq_years
                ) AS date_ref'));

                // Join with user data and aggregate
                $query = $query->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                    $join->on(DB::raw('YEAR(users.created_at)'), '=', 'date_ref.year');
                })
                    ->select(
                        'date_ref.year AS label',
                        DB::raw('COALESCE(SUM(CASE WHEN gender = "male" THEN 1 ELSE 0 END), 0) AS male'),
                        DB::raw('COALESCE(SUM(CASE WHEN gender = "female" THEN 1 ELSE 0 END), 0) AS female')
                    )
                    ->groupBy('date_ref.year')
                    ->orderBy('date_ref.year', 'DESC');
                break;

            default:
                throw new \Exception('Invalid duration');
                break;
        }

        $male = ['name' => 'Male'];
        $female = ['name' => 'Female'];
        $result = $query->get();
        $male['data'] = $result->pluck('male')->toArray();
        $female['data'] = $result->pluck('female')->toArray();
        $label = $result->pluck('label')->toArray();
        return compact('label', 'male', 'female');
    }
    private function getGamesPlayedGraph(Request $request)
    {
        $duration = $request->get('duration');
        $query = $this->user;

        switch ($duration) {
            case 'weekly':
                // Generate date references for the last 7 days
                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT CURDATE() - INTERVAL seq DAY AS date_label
                    FROM (
                        SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
                    ) AS seq_days
                ) AS date_ref'));

                $query = $query->join('game_submissions as gs', 'users.id', '=', 'gs.user_id')
                    ->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                        $join->on(DB::raw('DATE(gs.created_at)'), '=', 'date_ref.date_label');
                    })
                    ->select(
                        'date_ref.date_label AS label',
                        DB::raw('COALESCE(SUM(gs.game_id), 0) AS played')
                    )
                    ->groupBy('date_ref.date_label')
                    ->orderBy('date_ref.date_label', 'DESC');
                break;

            case 'monthly':
                $firstDayOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
                $lastDayOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT DATE_FORMAT(DATE_ADD("' . $firstDayOfMonth . '", INTERVAL seq DAY), "%Y-%m-%d") AS date_label
                    FROM (
                        SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL SELECT 30 UNION ALL SELECT 31
                    ) AS seq_days
                    WHERE DATE_ADD("' . $firstDayOfMonth . '", INTERVAL seq DAY) <= "' . $lastDayOfMonth . '"
                ) AS date_ref'));

                // Join with user data and aggregate
                $query = $query
                    ->join('game_submissions as gs', 'users.id', '=', 'gs.user_id')
                    ->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                        $join->on(DB::raw('DATE(gs.created_at)'), '=', 'date_ref.date_label');
                    })
                    ->select(
                        'date_ref.date_label AS label',
                        DB::raw('COALESCE(SUM(gs.game_id), 0) AS played')
                    )
                    ->groupBy('date_ref.date_label')
                    ->orderBy('date_ref.date_label', 'ASC');

                break;

            case 'yearly':
                // Generate year references for the last 5 years
                $dateReferenceQuery = DB::table(DB::raw('(
                    SELECT YEAR(NOW()) - seq AS year
                    FROM (
                        SELECT 0 AS seq UNION ALL SELECT 1 #UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                    ) AS seq_years
                ) AS date_ref'));

                // Join with user data and aggregate
                $query = $query
                    ->join('game_submissions as gs', 'users.id', '=', 'gs.user_id')
                    ->rightJoinSub($dateReferenceQuery, 'date_ref', function ($join) {
                        $join->on(DB::raw('YEAR(gs.created_at)'), '=', 'date_ref.year');
                    })

                    ->select(
                        'date_ref.year AS label',
                        DB::raw('COALESCE(COUNT(gs.game_id), 0) AS played')
                    )
                    ->groupBy('date_ref.year')
                    ->orderBy('date_ref.year', 'ASC');
                break;

            default:
                throw new \Exception('Invalid duration');
                break;
        }


        $result = $query->get();
        $data = $result->pluck('played')->toArray();
        $data = array(['name' => 'Played', 'data' => $data]);
        $label = $result->pluck('label')->toArray();
        return compact('label', 'data');
    }
    private function getCountriesGraph(Request $request)
    {
        $query = $this->user->rightjoin('countries', 'users.country_id', '=', 'countries.id')
            ->select('countries.name as country', DB::raw('COUNT(users.id) as total'))
            ->groupBy('countries.name')
            ->orderBy('total', 'DESC')->limit(4)
            ->get();
        $label = $query->pluck('country')->toArray();
        $data = $query->pluck('total')->toArray();
        return compact('label', 'data');
    }

    private function getDateRange(Carbon $from, Carbon $to)
    {
        $range = CarbonPeriod::create($from, $to)->toArray();
        $arr = [];
        foreach ($range as $value) {
            $arr[] = $value->format('Y-m-d');
        }
        return $arr;
    }

    public function getUsers($type, GetGraphsRequest $request)
    {
        try {
            $duration = $request->get('duration');
            $data = $this->countUser($duration,$type);
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function countUser($duration,$type)
    {
        $data['count'] = 0;
        $query = $this->user;
        $status = ($type == 'active') ? true : false;
        switch ($duration) {
            case 'weekly':
                $query = $query->where('created_at' ,'>=' ,now()->subDays(7));
                break;
            case 'monthly':
                $query = $query->where('created_at' ,'>=' ,now()->startOfMonth());
                break;
            case 'yearly':
                $query = $query->where('created_at' ,'>=' ,now()->startOfYear());
                break;
            default:
            throw new \Exception('Invalid duration');
                break;
        }
        $query = $query->when($status,function ($q) {
           $q->where('status',1); 
        });
        $data['count'] = $query->count();
        return $data;
    }
}
