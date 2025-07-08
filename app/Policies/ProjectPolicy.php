<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project)
    {
        return $user->hasRole('admin') 
                ||($user->hasRole('team_owner') && $user->id === $project->team->owner_id) 
                ||($user->hasRole('project_manager') && $user->id === $project->created_by_user_id)
        ? Response::allow()
        : Response::deny("You don't have permission to show this project");
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
     */
    public function update(User $user, Project $project)
    {
        return  $user->hasRole('admin') 
                ||(($user->hasRole('team_owner') && $user->id === $project->team->owner_id))
                ||($user->hasRole('project_manager') && $project->project_manager->id === $user->id)
        ? Response::allow()
        : Response::deny('You do not have the permissions to update this project.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project)
    {
        return  $user->hasRole('admin') 
                ||(($user->hasRole('team_owner') && $user->id === $project->team->owner_id))
                ||(($user->hasRole('project_manager') && $user->id === $project->created_by_user_id))
        ? Response::allow()
        : Response::deny('You do not have the permissions to delete this project.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can change the project manager for the project.
     */
    public function change(User $user, Project $project)
    {
        return  $user->hasRole('admin') 
                ||(($user->hasRole('team_owner') && $user->id === $project->team->owner_id))
        ? Response::allow()
        : Response::deny('You do not have the permissions to change project manager for this project.');
    }

    /**
     * Determine whether the user can add workers to the project .
     */
    public function add(User $user, Project $project)
    {
        return  $user->hasRole('admin') 
                ||(($user->hasRole('team_owner') && $user->id === $project->team->owner_id))
                ||($user->hasRole('project_manager') && $project->project_manager->id === $user->id)
        ? Response::allow()
        : Response::deny('You do not have the permissions to add workers to this project.');
    }

    /**
     * Determine whether the user can add workers to the project .
     */
    public function remove(User $user, Project $project)
    {
        return  $user->hasRole('admin') 
                ||(($user->hasRole('team_owner') && $user->id === $project->team->owner_id))
                ||($user->hasRole('project_manager') && $project->project_manager->id === $user->id)
        ? Response::allow()
        : Response::deny('You do not have the permissions to remove workers to this project.');
    }

    /**
     * Determine whether the user can comment on the project.
     */
    public function comment(User $user , Project $project)
    {
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $project->team->owner_id))
                    || $project->workers->contains($user)
            ? Response::allow()
            
            : Response::deny('You do not have the permissions to comment at this project .');
    }

    /**
     * Determine whether the user can add attachments on the project.
     */
    public function attachable(User $user , Project $project)
    {
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $project->team->owner_id))
                    || $project->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to add attachments to this project .');
    }

}
