<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('admin')
            ? Response::allow()
            : Response::deny('You do not have the permissions to view all notifications .');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Notification $notification)
    {
        return $user->hasRole('admin')
                    || $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to show to this notification .');
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
    public function update(User $user, Notification $notification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Notification $notification)
    {
        return $user->hasRole('admin')
                    || $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You do not have the permissions to delete to this notification .');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Notification $notification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        return false;
    }

    /**
     * Determine whether the user can mark as read the model.
     */
    public function mark(User $user, Notification $notification)
    {
        return $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You do not have permissions to mark this notification as read.');
    }
}
