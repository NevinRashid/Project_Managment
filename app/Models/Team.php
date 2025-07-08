<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Team extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'owner_id',
    ];

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
     * Scope a query to only the current user's teams
     *
     */
    public function scopeOwnTeams($query)
    {
        return $query->where('owner_id', Auth::user()->id);
    }

    /**
     * Get the user who owns this team.
     */
    public function owner(){
        return $this->belongsTo(User::class);
    }

    /**
     * Get all users who are members of this team.
     */
    public function members(){
        return $this->belongsToMany(User::class,'team_user','team_id','user_id');
    }

    /**
     * Get all the projects this team is working on.
     */
    public function projects(){
        return $this->hasMany(Project::class,'team_id');
    }
}
