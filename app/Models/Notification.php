<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
    ];

    /**
     * Get the user to whom the notification was sent.
     */
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

}
