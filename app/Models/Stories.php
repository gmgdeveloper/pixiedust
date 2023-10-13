<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stories extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'caption',
        'media_url',
        'type',
        'tagged_users',
        'allowComments',
        'created_at', 
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
