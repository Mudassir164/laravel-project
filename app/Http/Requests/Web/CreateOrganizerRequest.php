<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateOrganizerRequest extends FormRequest
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
            'name'              =>  'required|min:3|max:120',
            'user_name'         =>  'required|min:3|max:120',
            'email'             =>  ['required', 'email', Rule::unique('web_users', 'email')->where(function ($q) {
                $q->where('deleted_at', '=', NULL);
            })],
            'phone'             =>  'required|numeric',
            'password'          =>  'required',
            'confirm_password'  =>  'required|same:password'
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
