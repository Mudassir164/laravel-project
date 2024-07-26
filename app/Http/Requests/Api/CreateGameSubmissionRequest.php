<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class CreateGameSubmissionRequest extends FormRequest
{

    // public function prepareForValidation(): void
    // {
    //     $result = $this->get('results');
    //     if(gettype($result) == 'array') {
    //         unset($this['results']);
    //         $this->merge(['results' => array_values($result)]);
    //     }
    //     dd($this->all(),$this->get('results'));
    // }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'game_id' => 'required|exists:games,id,deleted_at,NULL',
            'results' => 'required|array',
            'results.*.game_question_id' => 'required|distinct|exists:game_questions,id,game_id,' . $this->get('game_id'),
            'results.*.game_question_option_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if(!is_null($value)){
                        // Get the corresponding game_question_id for the current answer
                        $index = intval(explode('.', $attribute)[1]);
                        $question = $this->get('results')[$index];
                        // Validate if the provided game_question_option_id belongs to the specific game_question_id
                        if (!\App\Models\GameQuestionOption::where('game_question_id', $question['game_question_id'])
                            ->where('id', $value)
                            ->exists()) {
                            $fail("The $attribute is invalid.");
                        }
                    }
                },
            ],
            // 'completion_time' => 'required|date_format:H:i:s',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ],
            422
        ));
    }
}
