<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'path',
        'disk',
        'attachable_id',
        'attachable_type',
        'file_name',
        'file_size',
        'mime_type',
    ];

    /**
     * Get the parent attachable model (task or comment or project).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to return only attachments visible to the given user.
     */

    public function scopeVisibleTo($query, User $user)
    {
        // If the user has the admin role, he sees everything
        if ($user->hasRole('admin')) {
            return $query;
        }
        /* The rest of the cases :
        1-Projects in which the user is a worker, manager, or team owner
        2- Tasks belonging to user projects        
        */
        return $query->where(function ($q) use ($user) {
            $q->orWhereHasMorph('attachable', [\App\Models\Project::class], function ($q) use ($user) {
                    $q->whereHas('workers', fn($w) => $w->where('users.id', $user->id))
                    ->orWhereHas('team', fn($t) => $t->where('owner_id', $user->id));
                })
                ->orWhereHasMorph('attachable', [\App\Models\Task::class], function ($q) use ($user) {
                    $q->whereHas('project.workers', fn($w) => $w->where('users.id', $user->id));
                });
            });
    }
}
