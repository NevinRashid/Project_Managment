<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'content',
        'user_id',
    ];



    /**
     * Get the parent commentable model (task or project).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all of the attachments for the comment.
     * 
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments():MorphMany{
        return $this->morphMany(Attachment::class , 'attachable');
    }

    /**
     * Get the user who posted this comment.
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * This method is to clean the content from harmful tags.
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strip_tags($value),
        );
    }

    /**
     * Scope a query to return all project's comments.
     */
    public function scopeProjectComments($query)
    {
        return $query ->where('commentable_type','\App\Models\Project');
    }

    /**
     * Scope a query to return all tasks's comments.
     */
    public function scopeTaskComments($query)
    {
        return $query ->where('commentable_type','\App\Models\Task');
    }

    /**
     * Scope a query to only comments visible to given user.
     */
    public function scopeVisibleTo($query, User $user)
    {
        // If the user has the admin role, he sees everything
        if ($user->hasRole('admin')) {
            return $query; 
        }

        //The rest of the cases :
        return $query->where(function ($q) use ($user) {
        // 1- The commentator
        $q->where('user_id', $user->id)

        // 2- Comments on the projects he works on or manages, or the owner of the team to which the project belongs
        ->orWhereHasMorph('commentable', [\App\Models\Project::class], function ($q) use ($user) {
            $q->whereHas('workers', fn($w) => $w->where('users.id', $user->id))
                ->orWhereHas('team', fn($t) => $t->where('owner_id', $user->id));
        })

        // 3. Comments tasks within user projects
        ->orWhereHasMorph('commentable', [\App\Models\Task::class], function ($q) use ($user) {
            $q->whereHas('project.workers', fn($w) => $w->where('users.id', $user->id));
        });
    });
}
}
