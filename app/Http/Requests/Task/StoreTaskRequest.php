<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Models\Project;
use App\Rules\TaskRules\CanAssignTaskToUser;
use App\Rules\TaskRules\UserManageProject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user=Auth::user();
        return $user->can('create task');
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'assigned_user_id' => $this->assigned_user_id ?? Auth::user()->id,
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
            'name'                  => ['required', 'string','unique:tasks', 'max:255'],
            'description'           => ['nullable', 'string','max:1000'],
            'project_id'            => ['required', 'integer','exists:projects,id',new UserManageProject()],
            'assigned_user_id'      => ['integer', Rule::exists('project_user','user_id')->where('project_id',$this->project_id)
                                                                                    , new CanAssignTaskToUser($this->project_id)
                                        ],
            'status'                => ['nullable','in:pending, in_progress, completed, overdue, cancelled'],
            'due_date'              => ['required', 'date','after:'.now()->addHours(5),'after_or_equal:'.now()->addDays(5)],
            'priority'              => ['required','integer',new Enum(TaskPriority::class)],
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
            'project_id.required'           => 'The project is required please',
            'project_id.integer'            => 'The project id must be an integer',
            'project_id.exists'             => 'The value entered does not exist in the projects table.',
            'assigned_user_id.required'     => 'The user for this task is required please',
            'assigned_user_id.exists'       => 'The user you are trying to assign this task to is not working on the project',
            'name.required'                 => 'The name is required please.',
            'name.max'                      => 'The length of the name may not be more than 255 characters.',
            'name.unique'                   => 'The name must be unique and not duplicate. Please use another name',
            'description.max'               => 'The length of the description may not be more than 1000 characters.',
            'status.in'                     => 'The status must be one of (draft,active,completed,cancelled)',
            'due_date.date'                 => 'The duration date format is incorrect. Please write the appropriate date format.',
            'due_date.after'                => 'The task submission period must be a future date and at least more than five hours from now',
            'due_date.after_or_equal'       => 'The deadline for submitting the task must be more than or equal to 5 days from now.',
            'due_date.required'             => 'The duration date is required please.',
            'priority.required'             => 'The priority is required please.',
            'priority.integer'              => 'The priority must be an integer.',
            'priority.enum'                 => 'The value chosen for priority is invalid. Please choose a value (1, 2, 3, or 4).',

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
