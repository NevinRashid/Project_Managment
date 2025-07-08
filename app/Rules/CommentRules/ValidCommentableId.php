<?php

namespace App\Rules\CommentRules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCommentableId implements Rule
{

    protected $modelClass;

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
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
        return $this->modelClass::whereKey($value)->exists();
    }

        public function message(): string
    {
        return 'The selected commentable id is invalid in specified class.';
    }
}
