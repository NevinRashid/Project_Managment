<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

use function Termwind\parse;

class Project extends Model
{
    use HasRoles, HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'team_id',
        'description',
        'status',
        'due_date',
        'created_by_user_id',
    ];

    protected $guarded = [
        'created_by_user_id',
    ];
    /**
     * Get all of the comments for the project.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments():MorphMany{
        return $this->morphMany(Comment::class , 'commentable');
    }

    /**
     * Get all of the attachments for the project.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments():MorphMany{
        return $this->morphMany(Attachment::class , 'attachable');
    }

    /**
     * Get the user who created this project.
     */
    public function creator(){
        return $this->belongsTo(User::class,'created_by_user_id');
    }

    /**
     * Get users working on the project.
     */
    public function workers(){
        return $this->belongsToMany(User::class,'project_user','project_id','user_id')->withPivot('role');
    }

    /**
     * Get all Tasks for this project.
     */
    public function tasks(){
        return $this->hasMany(Task::class,'project_id');
    }

    /**
     * Get the team working on this project.
     */
    public function team(){
        return $this->belongsTo(Team::class);
    }

    /**
     * Get only completed tasks.
     */
    public function completedTasks()
    {
        return $this->tasks()->where('status','completed');
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
     * This method is to return the project manager.
     */
    protected function projectManager(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->workers->firstWhere(fn ($worker) => $worker->pivot->role==='project_manager')
        );
    }

    /**
     * Scope a query to return only active projects.
     */
    public function scopeActiveProjects($query)
    {
        return $query->where('status','active');
    }

    /**
     * Scope a query to return only completed projects.
     */
    public function scopeCompletedProjects($query)
    {
        return $query->where('status','completed');
    }

    /**
     * Scope a query to return over due_date projects.
     */
    public function scopeOverdueProjects($query)
    {
        $now = now()->format('Y-m-d H:i');
        return $query ->where('due_date','<', $now)
                        ->where('status' , '!=', 'completed');
    }


}
