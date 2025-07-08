<?php

namespace App\Rules\TaskRules;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CanAssignTaskToUser implements Rule
{
    protected $projectId;
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * This rule is intended to check the current user role.
     *  If his role is a member of this project, he can only assign tasks to himself.
     *  He cannot assign tasks to other members.
     *
     * @param $attribute, $value
     *
     * @return boolean
     */
    public function passes($attribute, $value): bool
    {
        $project = Project::find($this->projectId);
        if(!$project){
            $this->message('The project does not exist in the database');
            return false;
        }
        $role = $project->workers()->where('user_id', Auth::user()->id)?->first()?->pivot->role;
        if($role === 'member' && $value != Auth::user()->id)
        return false;

        else return true;
    }

        public function message(): string
    {
        return 'Members are only allowed to assign tasks to themselves';
    }
}

