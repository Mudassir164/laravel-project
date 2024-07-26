<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class RegisterUserRequest extends FormRequest
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
            'profile_pic' => 'nullable|file|mimes:png,jpg',
            'username' => 'required|unique:users,username,NULL,NULL,deleted_at,NULL',
            'name' => 'required',
            'gender' => 'required|in:male,female',
            'password' => 'required',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id'
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
