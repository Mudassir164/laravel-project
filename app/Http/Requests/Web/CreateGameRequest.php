<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateGameRequest extends FormRequest
{
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
            'name' => 'required',
            'sport_event_id' => 'required',
            'game_type_id' => 'required|exists:game_types,id,deleted_at,NULL',
            'game_category_id' => 'required|exists:game_categories,id,deleted_at,NULL',
            // 'limit' => 'required|date_format:H:i',
            // 'start_date' => 'required|date|date_format:Y-m-d|after:today',
            // 'start_time' => 'required|date_format:H:i',
            'questions' => 'required|array',
            'questions.*.game_question_type_id' => 'required|exists:game_question_types,id,deleted_at,NULL',
            'questions.*.game_question_difficulty_id' => 'required|exists:game_question_difficulties,id,deleted_at,NULL',
            'questions.*.description' => 'required',
            'questions.*.duration' => 'required|date_format:H:i',
            'questions.*.options' => 'required|array',
            'questions.*.options.*.option' => 'required',
            'questions.*.options.*.correct_option' => 'required|boolean',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()], 
            422
        ));
    }
}
