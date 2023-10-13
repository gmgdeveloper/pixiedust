<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bookings;

class Bookings extends Model
{
    use HasFactory;
    
    protected $table = 'bookings';
    
    protected $fillable = [
        'user_id',
        'freelancer_id', 
        'service_id', 
        'service_amount', 
    ];

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'freelancer_id');
    }
}
