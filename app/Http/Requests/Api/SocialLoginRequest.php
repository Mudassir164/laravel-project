<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class SocialLoginRequest extends FormRequest
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
            'type' => 'required|in:google,facebook,apple',
            'platform_id' => 'required',
            'name' => 'required',
            'email' => 'sometimes|email',
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
