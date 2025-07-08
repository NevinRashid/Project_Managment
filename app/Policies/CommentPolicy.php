<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class CommentPolicy
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
    public function view(User $user, Comment $comment)
    {
        $model = $comment->commentable_type::findOrfail($comment->commentable_id);
        if( $model instanceof \App\Models\Project ){
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $model->team->owner_id))
                    || $model->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to comment at this project .');
        }
        elseif( $model instanceof \App\Models\Task){
            return $user->hasRole('admin')
                    || $user->roleInProject($model->project) === 'project_manager'
                    || $user->id === $model->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to comment at this task .');
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment)
    {
        return $user->id === $comment->user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to update this comment.');
        
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment)
    {
        $model = $comment->commentable_type::findOrfail($comment->commentable_id);
        if( $model instanceof \App\Models\Project ){
            return $user->hasRole('admin')
                    || ( $user->hasRole('team_owner') && ($user->id === $model->team->owner_id))
                    || $model->workers->contains($user)
            ? Response::allow()
            : Response::deny('You do not have the permissions to delete this comment .');
        }
        elseif( $model instanceof \App\Models\Task){
            return $user->hasRole('admin')
                    || $user->roleInProject($model->project) === 'project_manager'
                    || $user->id === $model->assigned_user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to delete this comment');
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
    /**
     * Determine whether the user can add attachments to the comment.
     */
    public function attachable(User $user , Comment $comment)
    {
            return $user->hasRole('admin')
                    || $user->id === $comment->user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to add attachments to this comment .');
    }
}
