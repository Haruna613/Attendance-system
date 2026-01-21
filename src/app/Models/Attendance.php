<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'punch_in',
        'punch_out',
        'remarks',
        'status',
    ];

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
