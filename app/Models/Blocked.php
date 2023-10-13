<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
    use HasFactory;
    
    protected $table = 'blocked';
    
    protected $fillable = [
        'user_id',
        'blocker_id', 
    ];
}
