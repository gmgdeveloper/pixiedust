<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;   
use App\Models\Blocked;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'bio', 
        'userName', 
        'dateOfBirth', 
        'image',
        'device_id',
        'enable_push_notifications',
        'status',
        'isFreelancer'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'user_id');
    }

    // public function following()
    // {
    //     return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    // }
    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    public function isFollowingMe($targetUserId, $userId)
    {
        return Follower::where('user_id', $userId)
            ->where('follower_id', $targetUserId)
            ->exists();
    }

    public function isFollowedByMe($targetUserId, $userId)
    {
        return Follower::where('user_id', $targetUserId)
        ->where('follower_id', $userId)
            ->exists();
    }

    public function blocked()
    {
        return $this->hasMany(Blocked::class, 'blocker_id');
    }


}
