<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateGameSubmissionRequest;
use App\Models\Game;
use App\Models\GameSubmission;
use App\Models\GameSubmissionDetail;
use App\Models\UserLeaderboard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function App\Helpers\checkAlreadyGameSubmission;
use function App\Helpers\checkGameSubmission;
use function App\Helpers\updateUserLeague;

class GameSubmissionController extends Controller
{
    private $gameSubmission;
    private $gameSubmissionDetail;
    private $game;
    private $userLeaderboard;

    public function __construct(
        GameSubmission $gameSubmission,
        GameSubmissionDetail $gameSubmissionDetail,
        Game $game,
        UserLeaderboard $userLeaderboard
    ) {
        $this->gameSubmission = $gameSubmission;
        $this->gameSubmissionDetail = $gameSubmissionDetail;
        $this->game = $game;
        $this->userLeaderboard = $userLeaderboard;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGameSubmissionRequest $request)
    {
        try {
            DB::beginTransaction();

            $game = $this->game->getFullGame($request->get('game_id'));
            // check game submission possibility
            checkGameSubmission($game);
            checkAlreadyGameSubmission($game, auth()->user());

            $gameSubmissionData = [
                'game_id' => $request->get('game_id'),
                'user_id' => auth()->user()->id,
                'completion_time' => $request->get('completion_time')
            ];

            $gameSubmission = $this->gameSubmission->createGameSubmission($gameSubmissionData);

            $answers = $request->get('results');
            $this->gameSubmissionDetail->submitGameAnswers($answers, $gameSubmission->id);

            $gameSubmission = $this->gameSubmission->findFullGameSubmission($gameSubmission->id);

            $userLeaderboard = $this->userLeaderboard->leaderboadCreateUpdate(
                [
                    'user_id' => $gameSubmission->user_id,
                    'score' => $gameSubmission->score,
                    'total_time' => $gameSubmission->completion_time,
                ]
            );
            updateUserLeague($userLeaderboard);

            $set['data'] = $gameSubmission;
            DB::commit();
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            DB::rollBack();
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GameSubmission  $gameSubmission
     * @return \Illuminate\Http\Response
     */
    public function show(GameSubmission $gameSubmission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GameSubmission  $gameSubmission
     * @return \Illuminate\Http\Response
     */
    public function edit(GameSubmission $gameSubmission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GameSubmission  $gameSubmission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GameSubmission $gameSubmission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GameSubmission  $gameSubmission
     * @return \Illuminate\Http\Response
     */
    public function destroy(GameSubmission $gameSubmission)
    {
        //
    }
}
