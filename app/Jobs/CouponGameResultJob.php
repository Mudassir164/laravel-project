<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\GameQuestionOption;
use App\Models\GameSubmission;
use App\Models\GameSubmissionDetail;
use App\Models\SportEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CouponGameResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $gameId;
    public $date;
    public $game;
    public function __construct($gameId, $date)
    {
        $this->gameId = $gameId;
        $this->date = $date;
        $this->game = new Game();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $game = $this->game->getFullGame($this->gameId);
            $eventId = $game->sport_event->sport_event_id ?? null;
            if ($eventId) {
                $sportEvent = Game::getSportEventDetails($eventId);
                $event = SportEvent::UpdateOrCreate(['sport_event_id' => $eventId], ['sport_event_obj' => $sportEvent]);
                $answers = [];
                $teams = [];

                if ($sportEvent->sport_event_status->match_status == 'ended') {
                    foreach ($sportEvent->sport_event->competitors as $competitors) {
                        $teams[$competitors->qualifier]['id'] = $competitors->id;
                        $teams[$competitors->qualifier]['name'] = $competitors->name;
                    }

                    if (isset($sportEvent->sport_event_status->winner_id)) {
                        if ($teams['home']['id'] == $sportEvent->sport_event_status->winner_id) {
                            array_push($answers, $teams['home']['name']);
                        } else if ($teams['away']['id'] == $sportEvent->sport_event_status->winner_id) {
                            array_push($answers, $teams['away']['name']);
                        }
                    } else {
                        array_push($answers, 'None');
                    }

                    if (count($sportEvent->sport_event_status->period_scores) > 0) {
                        $scores = $sportEvent->sport_event_status->period_scores;
                        if ($scores[0]->home_score > $scores[0]->away_score) {
                            array_push($answers, $teams['home']['name']);
                        } elseif ($scores[0]->home_score < $scores[0]->away_score) {
                            array_push($answers, $teams['away']['name']);
                        } else {
                            array_push($answers, 'None');
                        }

                        if ($scores[1]->home_score > $scores[1]->away_score) {
                            array_push($answers, $teams['home']['name']);
                        } elseif ($scores[1]->home_score < $scores[1]->away_score) {
                            array_push($answers, $teams['away']['name']);
                        } else {
                            array_push($answers, 'None');
                        }

                        if ($sportEvent->sport_event_status->home_score > $sportEvent->sport_event_status->away_score) {
                            array_push($answers, $teams['home']['name']);
                        } elseif ($sportEvent->sport_event_status->home_score < $sportEvent->sport_event_status->away_score) {
                            array_push($answers, $teams['away']['name']);
                        } else {
                            array_push($answers, 'None');
                        }
                    }

                    if (count($answers) > 0) {
                        foreach ($game->questions as $key => $question) {
                            GameQuestionOption::where(['game_question_id' => $question->id, 'option' => $answers[$key]])->update(['correct_option' => 1]);
                        }
                    }

                    foreach ($game->gameSubmissions as $gameSubmissions) {
                        $score = 0;
                        foreach ($gameSubmissions->gameSubmissionDetails as $gameSubmissionDetail) {
                            if ($gameSubmissionDetail->gameQuestionOption && $gameSubmissionDetail->gameQuestionOption->correct_option) {
                                GameSubmissionDetail::where(['id' => $gameSubmissionDetail->id])->update(['correct_answer' => 1]);
                                $score += $gameSubmissionDetail->GameQuestionDifficulty->points;
                            }
                        }
                        GameSubmission::where('id', $gameSubmissions->id)->update(['score' => $score]);
                    }

                    $dispatchDate = $this->date->addMinutes(20);
                    GameResultJob::dispatch($game->id)->delay($dispatchDate);
                } else {
                    $dispatchDate = $this->date->addMinutes(20);
                    CouponGameResultJob::dispatch($game->id, $dispatchDate)->delay($dispatchDate);
                }
            }

        } catch (\Exception $e) {
            // Log the exception for further investigation
            Log::error('CouponGameResultJob failed: ' . $e->getMessage());

            // Handle the exception as per your application's needs
            // For example, retry the job or mark it as failed
            $this->release(10); // Retry after 10 seconds
        }
    }
}
