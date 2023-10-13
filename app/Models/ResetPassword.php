<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    use HasFactory;
    public $table = 'password_resets';
    public $timestamps = false;
    protected $PrimaryKey = 'user_email';
    public $incrementing = false;
    
    protected $fillable = [
        'user_email',
        'token',
        'created_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->primaryKey = 'user_email';
    }
}
