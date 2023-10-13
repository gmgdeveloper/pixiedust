<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class AdminLogin extends Model
{
    use HasFactory;
    use HasApiTokens;

    protected $fillable = [
        'admin_id', 
        'user_name',
        'user_email',
        'user_password',
    ];

}
