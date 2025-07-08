<?php

namespace App\Http\Requests\Project;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user=Auth::user();
        return $user->can('create project');
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $userId = Auth::user()->id;
        $workers = $this->input('worker_ids',[]);
        $workers[] = $userId;
        $this->merge([
            'worker_ids'           => collect($workers)->filter()->unique()->toArray(),
            'status'               =>'draft',
            'attachments'          => $this->attachments ?? []
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
            'name'              => ['required', 'string','unique:projects', 'max:255'],
            'description'       => ['required', 'string','max:1000'],
            'due_date'          => ['required', 'date','after:today'],
            'team_id'           => ['required','exists:teams,id'],
            'worker_ids'        => ['nullable','array'],
            'worker_ids.*'      => ['integer',Rule::exists('team_user','user_id')->where('team_id',$this->team_id)],
            'status'            => ['nullable','in:draft,active,completed,cancelled'],
            'attachments'       => ['nullable','array','max:5'],
            'attachments.*'     => ['file','mimes:jpg,jpeg,png,pdf','mimetypes:image/jpg,image/jpeg,image/png,application/pdf','max:5120'],
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
            'name.required'                 => 'The name is required please.',
            'name.max'                      => 'The length of the name may not be more than 255 characters.',
            'name.unique'                   => 'The name must be unique and not duplicate. Please use another name',
            'description.required'          => 'The description is required please.',
            'description.max'               => 'The length of the description may not be more than 1000 characters.',
            'due_date.date'                 => 'The duration date format is incorrect. Please write the appropriate date format.',
            'due_date.after'                => 'The date must be a future date.',
            'due_date.required'             => 'The duration date is required please.',
            'team_id.required'              => 'The team is required please',
            'team_id.exists'                => 'The value entered does not exist in the teams table.',
            'worker_ids.array'              => 'The worker ids field must be an array.',
            'worker_ids.*.exists'           => 'One of the users you add to the project is not a member of the team.',
            'status.in'                     => 'The status You must be one of (draft,active,completed,cancelled)',
            'attachments.array'             => 'The attachments must be a an array',
            'attachments.max'               => 'maximum number of attachments is 5.',
            'attachments.*.file'            => 'The attachment must be an file',
            'attachments.*.mimes'           => 'The attachment must be a file of type: jpg,jpeg,png,pdf',
            'attachments.*.max'             => 'The attachment size must not exceed 5 MB',
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
