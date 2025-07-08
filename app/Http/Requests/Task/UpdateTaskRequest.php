<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Rules\TaskRules\CanAssignTaskToUser;
use App\Rules\TaskRules\UserManageProject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

use function PHPUnit\Framework\isEmpty;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user=Auth::user();
        return $user->can('update task');
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //Initialize the project_id if not sent with the old value because we will need it with the validation rule later.
        $this->merge([
            'project_id' => $this->project_id ?? $this->route('task')?->project_id
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
            'name'                  => ['nullable', 'string','unique:tasks', 'max:255'],
            'description'           => ['nullable', 'string','max:1000'],
            'project_id'            => ['nullable', 'integer','exists:projects,id',new UserManageProject()],
            'assigned_user_id'      => ['integer',Rule::exists('project_user','user_id')->where('project_id',$this->project_id),
                                                                                        new CanAssignTaskToUser($this->project_id)
                                        ],
            'status'                => ['nullable','in:pending,in_progress,completed,cancelled'],
            'due_date'              => ['nullable', 'date','after:'.now()->addHours(5),'before_or_equal:'.now()->addDays(5)],
            'priority'              => ['nullable','integer',new Enum(TaskPriority::class)],
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
            'name.max'                      => 'The length of the name may not be more than 255 characters.',
            'name.unique'                   => 'The name must be unique and not duplicate. Please use another name',
            'description.max'               => 'The length of the description may not be more than 1000 characters.',
            'project_id.integer'            => 'The project id must be an integer',
            'project_id.exists'             => 'The value entered does not exist in the projects table.',
            'assigned_user_id.exists'       => 'The user you are trying to assign this task to is not working on the project',
            'status.in'                     => 'The status You must be one of (draft,active,completed,cancelled)',
            'due_date.date'                 => 'The duration date format is incorrect. Please write the appropriate date format.',
            'due_date.after'                => 'The task submission period must be a future date and at least more than five hours from now',
            'due_date.before_or_equal'      => 'The deadline for submitting the task must be less than or equal to 5 days from now.',
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
