<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSubmission;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private $game;
    private $gameSubmission;

    public function __construct(Game $game, GameSubmission $gameSubmission)
    {
        $this->game = $game;
        $this->gameSubmission = $gameSubmission;
    }

    public function list($eventID, Request $request)
    {
        try {
            $set = $this->game->list($eventID, $request);
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function getGame($id)
    {
        try {
            $set = [];
            $data = $this->game->getFullGame($id);
            $set['data'] = $data;
            if (!$data)
                $set['data'] = [];
            $set['message'] = 'resource not found';
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }

    public function userGameList(Request $request)
    {
        try {
            $set = [];
            $limit = $request->limit ?? 10;

            $data = $this->gameSubmission->getGameSubmissions([
                'user_id' => auth()->user()->id
            ], [])->paginate($limit)->toArray();

            // $data = $this->game->getUserGameList([], [
            //     'user_id' => auth()->user()->id
            // ])->paginate($limit)->toArray();

            $set['data'] = $data['data'];
            unset($data['data']);
            $set['page'] = $data;
            return $this->responseToClient($set);
        } catch (\Exception $th) {
            return $this->responseToClient(['message' => $th->getMessage()], 400);
        }
    }
    
}
