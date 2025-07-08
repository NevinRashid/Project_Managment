<?php

namespace App\Rules\TaskRules;

use App\Models\Project;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserManageProject implements Rule
{
    /**
     * This rule to verify that the current user is among the workers
     * for the project in which he is trying to assign a task.
     * 
     * @param $attribute, $value
     * 
     * @return boolean
     */
    public function passes($attribute, $value): bool
    {
        return Project::where('id', $value)
                        ->whereHas('workers', fn($q) =>
                            $q->where('user_id', Auth::user()->id)
                        )->exists();
    }

        public function message(): string
    {
        return 'You are not a worker on the project you are trying to add a task to, please change the project.';
    }
}
