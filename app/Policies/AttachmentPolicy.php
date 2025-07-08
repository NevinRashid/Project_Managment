<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttachmentPolicy
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
    public function view(User $user, Attachment $attachment)
    { 
        $model = $attachment->attachable_type::findOrfail($attachment->attachable_id);
        if( $model instanceof \App\Models\Project ){
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $model->team->owner_id))
                    || $model->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to add attachment to this project .');
        }
        elseif( $model instanceof \App\Models\Task){
            return $user->hasRole('admin')
                    || $user->roleInProject($model->project) === 'project_manager'
                    || $user->id === $model->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to add attachment to this task .');
        }
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
    public function update(User $user, Attachment $attachment)
    {
        $model = $attachment->attachable_type::findOrfail($attachment->attachable_id);
        if( $model instanceof \App\Models\Project ){
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $model->team->owner_id))
                    || $model->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to update this attachment.');
        }
        elseif( $model instanceof \App\Models\Task){
            return $user->hasRole('admin')
                    || $user->roleInProject($model->project) === 'project_manager'
                    || $user->id === $model->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to update this attachment ');
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attachment $attachment)
    {
        $model = $attachment->attachable_type::findOrfail($attachment->attachable_id);
        if( $model instanceof \App\Models\Project ){
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $model->team->owner_id))
                    || $model->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to delete this attachment ');
        }
        elseif( $model instanceof \App\Models\Task){
            return $user->hasRole('admin')
                    || $user->roleInProject($model->project) === 'project_manager'
                    || $user->id === $model->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to delete attachment.');
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attachment $attachment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attachment $attachment): bool
    {
        return false;
    }
}
