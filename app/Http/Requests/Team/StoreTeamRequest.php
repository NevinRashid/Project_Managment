<?php

namespace App\Http\Requests\Team;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user=Auth::user();
        return $user->can('create team');
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $userId = Auth::user()->id;
        $members = $this->input('member_ids',[]);
        $members[] = $userId;
        $this->merge([
            'owner_id'   => $userId,
            'member_ids' => collect($members)->unique()->toArray()
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string','unique:teams', 'max:255'],
            'owner_id'      => ['required','exists:users,id'],
            'member_ids'    => ['nullable','array'],
            'member_ids.*'  => ['integer','exists:users,id'],
        ];
    }


    /**
     *  Get the error messages for the defined validation rules.
     *
     *  @return array<string, string>
     */
    public function messages():array
    {
        return[
            'name.required'        => 'The name is required please.',
            'name.max'             => 'The length of the name may not be more than 255 characters.',
            'name.unique'          => 'The name must be unique and not duplicate. Please use another name',
            'owner_id.required'    => 'The user is required please',
            'owner_id.exists'      => 'The value entered does not exist in the users table.',
            'member_ids.array'     => 'The member ids field must be an array.',
            'member_ids.exists'    => 'The values entered does not exist in the users table.',
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
