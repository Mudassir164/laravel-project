<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\GameReward;
use App\Models\GameSubmission;
use App\Models\UserReward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GameResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $gameId;
    public $game;

    public function __construct($gameId)
    {
        $this->gameId   =   $gameId;
        $this->game     =   new Game();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $game = $this->game->find($this->gameId);
            if ($game) {
                $gameSubmissions = GameSubmission::with(['user'])->where("game_id", $game->id)->where('score' , '!=', null)->where('score' , '!=', 0)->orderBy('score', 'DESC')->get();
                if (count($gameSubmissions) > 0) {
                    foreach ($gameSubmissions as $key => $submission) {
                        $reward = GameReward::where(['game_id' => $game->id, 'position_id' => $key + 1])->first();
                        if ($reward) {
                            UserReward::create([
                                'user_id' => $submission->user_id,
                                'position_id' => $key + 1,
                                'game_id' => $game->id,
                                'reward_id' => $reward->reward_id,
                                'web_user_id' => $game->web_user_id,
                                'score' => $submission->score
                            ]);
                        }
        
                        if ($key + 1 == 10)
                            break;
                    }
                }
            }
        } catch (\Exception $th) {
            Log::error($th->getMessage(),$th->getTrace());
        }
    }
}
