<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CreateGameRequest;
use App\Libraries\SportsRadar;
use App\Models\{Game, GameType, Sponsorship, GameQuestion, GameQuestionDifficulty, GameCategory, GameSubmission, Position, QuestionTemplate, SportEvent, UserReward, WebUser, User};
use Illuminate\Http\Request;
use App\Http\Requests\Web\{
    AddGameQuestionDifficultyRequest,
    CouponQuestionListRequest,
    GameAlreadyExistRequest,
};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB,Log};

use function App\Helpers\getAllWebUserIds;

class GameController extends Controller
{
    private $game;
    private $gameType;
    private $GameQuestionDifficulty;
    private $GameCategory;
    private $questionTemplate;
    private $webUser;
    private $user;
    private $gameQuestion;
    private $sponsorship;

    public function __construct(
        Game $game,
        GameType $gameType,
        GameQuestionDifficulty $GameQuestionDifficulty,
        GameCategory $GameCategory,
        QuestionTemplate $questionTemplate,
        WebUser $webUser,
        User $user,
        GameQuestion $gameQuestion,
        Sponsorship $sponsorship
    ) {
        $this->game = $game;
        $this->gameType = $gameType;
        $this->GameQuestionDifficulty = $GameQuestionDifficulty;
        $this->GameCategory = $GameCategory;
        $this->questionTemplate = $questionTemplate;
        $this->webUser = $webUser;
        $this->user = $user;
        $this->gameQuestion = $gameQuestion;
        $this->sponsorship = $sponsorship;
    }

    public function gameStore(CreateGameRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->merge(['web_user_id' => auth()->id()]);
            $data = $this->game->gameStore($request);
            $event = $data['event'];
            unset($data['event']);
            DB::commit();
            $this->game->processJobs($data['data'],$event);
            return $this->responseToClient($data);
        } catch (\Exception $th) {
            DB::rollBack();
            Log::error($th->getTraceAsString());
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function gameDetails()
    {
        try {
            $data = $this->game->get();
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function showGames(Request $request)
    {
        try {
            $limit = $request->get('limit') ?? 10;
            $search = $request->get('search');
            $sortBy = $request->get('sortBy') ?? 'created_at';
            $order = $request->get('order') ?? 'desc';
            $id = auth()->id();

            $webUserIds = $request->has('web_user_ids') ? $request->web_user_ids : [];

            $gamesQuery = $this->game
                ->join('game_types', 'games.game_type_id', '=', 'game_types.id')
                ->select('games.*', 'game_types.name as game_type_name');

            if ($search) {
                $gamesQuery = $gamesQuery->where('games.name', 'like', '%' . $search . '%');
            }

            if ($request->organizer_id) {
                $gamesQuery = $gamesQuery->where('web_user_id', $request->organizer_id);
                $user = $this->webUser->getFullUserData($request->organizer_id);
            } else {
                $gamesQuery = $gamesQuery->when($webUserIds && count($webUserIds) > 0, function ($query) use ($webUserIds, $id) {
                    $query->whereIn('web_user_id', $webUserIds)->orWhere('web_user_id', $id);
                })->where('web_user_id', $id);
            }

            $games = $gamesQuery->orderBy($sortBy, $order)->paginate($limit);
            

            $games->getCollection()->transform(function ($game) {
                $game->no_of_game_questions = GameQuestion::where('game_id', $game->id)->count();
                return $game;
            });

            $data = $games->items();
            $page = $games->toArray();
            $web_user = $user ?? null;
            unset($page['data']);

            $response = compact('data', 'page', 'web_user');
            $response['message'] = 'Games are showing successfully!';

            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function gameCategories()
    {
        try {
            $data = $this->GameCategory->get();
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function getGameTypes()
    {
        try {
            $data = $this->gameType->getGameTypes();
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function addGameQuestionDifficulty(AddGameQuestionDifficultyRequest $request)
    {
        try {
            $data = $this->GameQuestionDifficulty->addGameQuestionDifficulty($request);
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function showGameQuestionDifficulty()
    {
        try {
            $data = $this->GameQuestionDifficulty->get();
            $response = compact('data');
            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function gameAlreadyExist(GameAlreadyExistRequest $request)
    {
        try {
            $webUserIds = getAllWebUserIds();
            $data = $this->game->getUserGameList(
                [
                    'game_type_id' => $request->game_type_id
                    // 'web_user_id' => auth()->user()->id
                ],
                [
                    'sport_event_id' => $request->sport_event_id,
                    'web_user_ids' => $webUserIds
                ]
            )->first();

            if ($data) {
                return $this->responseToClient(['message' => 'Game for this event already exist'], 500);
            }
            return $this->responseToClient([]);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function couponQuestions(CouponQuestionListRequest $request)
    {
        try {
            $questions = $this->questionTemplate->getAllQuestions(['status' => 1])->toArray();
            $data = [];

            foreach ($questions as $question) {
                $question = str_replace('[team_a]', $request->team_a, $question);
                $question = str_replace('[team_b]', $request->team_b, $question);
                array_push($data, $question);
            }
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function getAllPositions()
    {
        try {
            $data = Position::all();
            return $this->responseToClient(compact('data'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    public function gameList($id, Request $request)
    {
        try {
            $limit = $request->get('limit') ?? 5;

            $games = $this->game->where('sport_event_id', $id)->paginate($limit);

            // $gamesCollection = collect($games->items())->map(function ($game) {
            //     $game->no_of_sponsorships_accepted = Sponsorship::where('game_id', $game->id)
            //         ->where('status', 1)
            //         ->count();
            //     return $game;
            // });

            // $games->setCollection($gamesCollection);

            $data = $games->items();
            $page = $games->toArray();
            unset($page['data']);

            return $this->responseToClient(compact('data', 'page'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function pubHome()
    {
        try {
            $webUserIds = getAllWebUserIds();

            $gameIds = $this->game->whereIn('web_user_id', $webUserIds)->pluck('id');

            $currentMonth = now()->month;
            $currentYear = now()->year;

            $userCount = GameSubmission::whereIn('game_id', $gameIds)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->distinct('user_id')
                ->count('user_id');

            $winnersCount = UserReward::whereIn('game_id', $gameIds)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->distinct('user_id')
                ->count('user_id');

            $onGoingGames = $this->game->onGoingGames($gameIds);
            $upcomingGames = $this->game->upcomingGames($gameIds);

            $response = [
                'active_users_this_month' => $userCount ?? 0,
                'win_games_this_month' => $winnersCount ?? 0,
                'on_going_games' => $onGoingGames,
                'upcoming_games' => $upcomingGames
            ];

            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function adminHome()
    {
        try {
            $gameIds = $this->game->getAllGames()->pluck('id');

            $currentMonth = now()->month;
            $currentYear = now()->year;

            $userCount = GameSubmission::whereIn('game_id', $gameIds)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->distinct('user_id')
                ->count('user_id');
            $winnersCount = UserReward::whereIn('game_id', $gameIds)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->distinct('user_id')
                ->count('user_id');

            $registerUsersCount = $this->user->getCurrentMonthRegisteredUsers()->count();

            $onGoingGames = $this->game->onGoingGames($gameIds);
            $upcomingGames = $this->game->upcomingGames($gameIds);

            $response = [
                'active_users_this_month' => $userCount,
                'register_users_this_month' => $registerUsersCount ?? 0,
                'win_games_this_month' => $winnersCount,
                'on_going_games' => $onGoingGames,
                'upcoming_games' => $upcomingGames
            ];

            return $this->responseToClient($response);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function gameLeaderboard($id)
    {
        try {
            $data = $this->user->getUserLeaderboardByGame($id);
            $top_three = array_slice($data->toArray(), 0, 3);
            $questions = $this->gameQuestion->getAllGameQuestion(['game_id' => $id])->get();
            $sponsorship = $this->sponsorship->getAllSponsorship(['game_id' => $id])->get();
            return $this->responseToClient(compact('data', 'top_three', 'questions', 'sponsorship'));
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function eventLineup($id)
    {
        $sport = new SportsRadar();
        $event = $sport->eventLineUp($id);
        return response($event);
    }
}
