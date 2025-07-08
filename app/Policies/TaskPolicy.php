<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     * Who can view the task (the actual project manager or the person assigned to the project)
     */
    public function view(User $user, Task $task)
    {
        return ($user->roleInProject($task->project_id) === 'project_manager')
                || $user->id === $task->assigned_user_id
        ? Response::allow()
        : Response::deny('You do not have the permissions to show this task.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Who can update the task (the actual project manager or the person assigned to the project)
     */
    public function update(User $user, Task $task)
    {
        return ($user->roleInProject($task->project_id) === 'project_manager')
                || $user->id === $task->assigned_user_id
        ? Response::allow()
        : Response::deny('You do not have the permissions to update this task.');
    }

    /**
     * Determine whether the user can delete the model.
     * Who can delete the task (the actual project manager or the person assigned to the project)
     */
    public function delete(User $user, Task $task)
    {
        return ($user->roleInProject($task->project_id) === 'project_manager')
                || $user->id === $task->assigned_user_id
        ? Response::allow()
        : Response::deny('You do not have the permissions to delete this task.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Determine whether the user can comment on the task.
     */
    public function comment(User $user , Task $task)
    {
            return $user->hasRole('admin')
                    || $user->roleInProject($task->project) === 'project_manager'
                    || $user->id === $task->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to comment at this task .');

    }

    /**
     * Determine whether the user can add attachments to the task.
     */
    public function attachable(User $user , Task $task)
    {
            return $user->hasRole('admin')
                    || $user->roleInProject($task->project) === 'project_manager'
                    || $user->id === $task->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to add attachments to this task .');

    }

    /**
     * Determine whether the user can assign a task.
     */
    public function assign(User $user , Task $task)
    {
            return $user->hasRole('admin')
                    || $user->roleInProject($task->project) === 'project_manager'
                    || $user->id === $task->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to assign a task.');

    }

}
