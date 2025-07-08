<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'min:8', 'max:16'],
        ];
    
        return $rules;
    }

    
    /**
     *  Get the error messages for the defined validation rules.
     * 
     *  @return array<string, string>
     */
    public function messages():array
    {
        return[
            'email.required'      => 'The Email is required please',
            'email.email'         => 'Please adhere to the email format (example@gmail.com)',
            'email.max'           => 'The length of the email may not be more than 255 characters',
            'password.required'   => 'The password is required please',
            'password.min'        => 'Password must be at least 8 characters long',
            'password.max'        => 'The length of the password may not be more than 16 characters',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * @param  \Illuminate\Validation\Validator  $validator
     * 
     * @return void
     */
    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json
            ([
                'success' => false,
                'message' => 'Data validation error',
                'errors'  => $validator->errors()
            ] , 422));
    }
}
