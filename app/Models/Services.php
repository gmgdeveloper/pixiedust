<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'description',
        'price',
        'show_price', 
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
