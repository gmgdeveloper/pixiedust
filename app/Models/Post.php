<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Like;
use App\Models\Comment;
use App\Models\User;

class Post extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'media_url', 
        'caption', 
        'likes_count', 
        'comments_count',
        'type',
        'allowComments',
        'tagged_users',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLikedByMeAttribute()
    {
        $user = auth()->user();
        if ($user) {
            return $this->likes()->where('user_id', $user->id)->exists();
        }
        return false;
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

}
