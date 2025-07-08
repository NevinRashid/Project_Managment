<?php

namespace App\Models;

use App\Enums\TaskPriority;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'project_id',
        'assigned_user_id',
        'status',
        'due_date',
        'priority',
    ];

    protected $casts = [
        'priority' => TaskPriority::class
    ];

    /**
     * Get all of the comments for the task.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments():MorphMany{
        return $this->morphMany(Comment::class , 'commentable');
    }

    /**
     * Get all of the attachments for the task.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments():MorphMany{
        return $this->morphMany(Attachment::class , 'attachable');
    }

    /**
     * Get the project this task belongs to.
     */
    public function project(){
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user assigned to perform this task.
     */
    public function assignee(){
        return $this->belongsTo(User::class,'assigned_user_id');
    }

    /**
     * This method is to clean the description content from harmful tags.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strip_tags($value),
        );
    }

    /**
     * This method is to format due date.
     */
    protected function dueDate(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Carbon::parse($value)->format('Y-m-d H:i'),
        );
    }

    /**
     * Scope a query to return only active tasks.
     */
    public function scopeActiveTasks($query)
    {
        return $query->where('status','active');
    }

    /**
     * Scope a query to return only completed tasks.
     */
    public function scopeCompletedTasks($query)
    {
        return $query->where('status','completed');
    }

    /**
     * Scope a query to return over due_date tasks.
     */
    public function scopeOverdueTasks($query)
    {
        $now = now()->format('Y-m-d H:i');
        return $query ->where('due_date','<', $now)
                        ->whereNotIn('status' , ['completed','overdue']);
    }
}
