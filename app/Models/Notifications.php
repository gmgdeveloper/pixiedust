<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'content',
        'photo_id',
        'story_id',
        'post_id',
        'from_user_id',
        'notification_type',
        'status',
    ];

    public function fromUser()
    {
        return $this->belongsTo(User::class);
    }
}
