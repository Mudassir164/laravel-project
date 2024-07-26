<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class RegisterPubOwnerRequest extends FormRequest
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
            'pub_name' => 'required',
            'owner' => 'required',
            'password' => 'required',
            'address' => 'required',
            'phone' => 'required|numeric',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'post_code' => 'required',
            'token' => 'required',
            'subscriptionId' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'token.required' => 'Stripe token is required!',
            'subscriptionId.required' => 'Subscription package is required!'
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
