<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SignupRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:20',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:6|max:20|confirmed',
        ];
    }

    public function messages() //OPTIONAL
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot be longer than 20 character',
            'email.required' => 'Email is required',
            'email.email' => 'Email is not correct',
            'email.unique' => 'This email already exists, please try login in',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'This phone number already exists, please try login in',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }
}
