<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSubmissionDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function gameQuestion()
    {
        return $this->belongsTo(GameQuestion::class);
    }

    public function gameQuestionOption()
    {
        return $this->belongsTo(GameQuestionOption::class);
    }

    public function GameQuestionDifficulty()
    {
        return $this->hasOneThrough(
            GameQuestionDifficulty::class,  // The final model we want to access
            GameQuestion::class,            // The intermediate model
            'id',                           // Foreign key on the intermediate model (GameQuestion)
            'id',                           // Foreign key on the final model (GameQuestionDifficulty)
            'game_question_id',             // Local key on the current model (GameSubmissionDetail)
            'game_question_difficulty_id'   // Local key on the intermediate model (GameQuestion)
        );
    }

    // public function submitGameAnswers($data = [], $submissionID)
    // {
    //     // correct options with their associated game questions
    //     $correct_options = GameQuestionOption::with('gameQuestion')
    //         ->where('correct_option', 1)
    //         ->whereIn('id', array_column($data, 'game_question_option_id'))
    //         ->get();

    //     // score by summing the points of the associated game questions
    //     $score = $correct_options->sum(function ($correct_option) {
    //         return $correct_option->gameQuestion->points ?? 0;
    //     });

    //     // $correct_options_id = $correct_options->pluck('id')->toArray();

    //     // Transform the data to include the necessary fields
    //     $set = collect($data)->transform(function ($item) use ($submissionID, $correct_options) {
    //         $item['game_submission_id'] = $submissionID;

    //         // correct option for the current question
    //         $correct_option = $correct_options->firstWhere('id', $item['game_question_option_id']);

    //         // user's answer matches the correct answer at least 50%
    //         $user_answer = $item['answer'] ?? '';
    //         $correct_answer_text = $correct_option->option ?? '';

    //         $similarity_percentage = 0;
    //         similar_text($user_answer, $correct_answer_text, $similarity_percentage);

    //         $item['correct_answer'] = $similarity_percentage >= 50;
    //         $item['created_at'] = now();
    //         $item['updated_at'] = now();
    //         return $item;
    //     });

    //     // Calculate the total completion time
    //     $completion_time = gmdate("H:i:s", array_sum(array_column($data, 'time_spent')));

    //     // Update the game submission with the score and completion time
    //     GameSubmission::where('id', $submissionID)->update([
    //         'score' => $score,
    //         'completion_time' => $completion_time
    //     ]);

    //     // Insert the transformed data into the database
    //     return $this->insert($set->toArray());
    // }

    public function submitGameAnswers($data = [], $submissionID)
    {
        // Fetch correct options with their associated game questions
        $correct_options = GameQuestionOption::with('gameQuestion')
            ->where('correct_option', 1)
            ->whereIn('id', array_column($data, 'game_question_option_id'))
            ->get();

        // Calculate the score by summing the points of the associated game questions
        $score = $correct_options->sum(function ($correct_option) {
            return $correct_option->gameQuestion->points ?? 0;
        });

        // Extract the IDs of the correct options
        $correct_options_id = $correct_options->pluck('id')->toArray();

        // Transform the data to include the necessary fields
        $set = collect($data)->transform(function ($item) use ($submissionID, $correct_options) {
            $item['game_submission_id'] = $submissionID;

            // Find the correct option for the current question
            $correct_option = $correct_options->firstWhere('id', $item['game_question_option_id']);

            // Determine if the user's answer matches the correct answer at least 50%
            $user_answer = strtolower($item['answer'] ?? '');
            $correct_answer_text = strtolower($correct_option->option ?? '');

            // Clean and tokenize the answers for better comparison
            $user_answer_tokens = $this->cleanAndTokenize($user_answer);
            $correct_answer_tokens = $this->cleanAndTokenize($correct_answer_text);

            // Compare tokens
            $match_percentage = $this->calculateMatchPercentage($user_answer_tokens, $correct_answer_tokens);

            // Consider the answer correct if the match percentage is 50% or higher
            $item['correct_answer'] = $match_percentage >= 50;
            $item['created_at'] = now();
            $item['updated_at'] = now();
            return $item;
        });

        // Calculate the total completion time
        $completion_time = gmdate("H:i:s", array_sum(array_column($data, 'time_spent')));

        // Update the game submission with the score and completion time
        GameSubmission::where('id', $submissionID)->update([
            'score' => $score,
            'completion_time' => $completion_time
        ]);

        // Insert the transformed data into the database
        return $this->insert($set->toArray());
    }

    private function cleanAndTokenize($text)
    {
        // Convert text to lowercase and split into words
        $tokens = preg_split('/\s+/', $text);

        // Remove common stop words (you can expand this list)
        $stop_words = ['i', 'am', 'is', 'are', 'the', 'a', 'an'];
        $tokens = array_diff($tokens, $stop_words);

        return $tokens;
    }

    private function calculateMatchPercentage($user_tokens, $correct_tokens)
    {
        // Check for negations in user answer
        $user_negated = $this->hasNegation($user_tokens);
        $correct_negated = $this->hasNegation($correct_tokens);

        // Calculate the intersection and total tokens for comparison
        $matches = array_intersect($user_tokens, $correct_tokens);
        $total_tokens = count($correct_tokens);

        // Adjust match count if negations are present
        if ($user_negated !== $correct_negated) {
            // If one is negated and the other is not, it should be considered not matching
            $matches = [];
        }

        if ($total_tokens == 0) {
            return 0;
        }

        return (count($matches) / $total_tokens) * 100;
    }

    private function hasNegation($tokens)
    {
        // Define common negation words
        $negations = ['not', 'no', 'never', 'none'];

        // Check if any negation word is present in the tokens
        foreach ($tokens as $token) {
            if (in_array($token, $negations)) {
                return true;
            }
        }

        return false;
    }

}
