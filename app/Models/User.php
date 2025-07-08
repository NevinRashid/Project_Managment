<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * This function ensures that the name is always in a specified format
     *  (the first letter is uppercase when reading, all letters are lowercase when writing)
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => strtolower($value),
        );
    }

    /**
     * Get all projects created by this user.
     */
    public function ownedProjects(){
        return $this->hasMany(Project::class,'created_by_user_id');
    }

    /**
     * Get all projects this user is working on.
     */
    public function assignedProjects(){
        return $this->belongsToMany(Project::class,'project_user','user_id','project_id')->withPivot('role');;
    }

    /**
     * This method is to return the actual user's role in the project.
     */
    public function roleInProject($projectId)
    {
        return $this->assignedProjects()->where('project_id',$projectId)?->first()?->pivot->role;
    }

    /**
     * Get all teams owned by this user.
     */
    public function ownedTeams(){
        return $this->hasMany(Team::class,'owner_id');
    }

    /**
     * Get all the teams this user belongs to.
     */
    public function memberTeams(){
        return $this->belongsToMany(Team::class,'team_user','user_id','team_id');
    }

    /**
     * Get all the tasks assigned to this user.
     */
    public function assignedTasks(){
        return $this->hasMany(Task::class,'assigned_user_id');
    }

    /**
     * Get all notifications sent to this user.
     */
    public function notifications(){
        return $this->hasMany(Notification::class,'user_id');
    }

    /**
     * Get all comments of this user.
     */
    public function comments(){
        return $this->hasMany(Comment::class,'user_id');
    }
}
