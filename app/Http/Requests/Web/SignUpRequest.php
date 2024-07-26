<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SignUpRequest extends FormRequest
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
            'email' => ['required','email',Rule::unique('web_users','email')->where(function($q){
                $q->where('email_verified_at','!=',NULL);
            })]
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // $errors = array();
        // $messageBag = $validator->errors();
        // foreach($messageBag->keys() as $fieldKey){
        //     $errors[ $fieldKey ] = $messageBag->first($fieldKey);
        // }
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()], 
            422
        ));
    }
}
