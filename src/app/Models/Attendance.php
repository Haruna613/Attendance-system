<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'applied_at',
    ];

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalRestTimeAttribute()
    {
        $totalSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->end_time) {
                $totalSeconds += Carbon::parse($rest->start_time)->diffInSeconds(Carbon::parse($rest->end_time));
            }
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds / 60) % 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getActualWorkingTimeAttribute()
    {
        if (!$this->punch_out) return '-';

        $start = Carbon::parse($this->punch_in);
        $end = Carbon::parse($this->punch_out);

        $staySeconds = $start->diffInSeconds($end);

        $restSeconds = 0;
        foreach ($this->rests as $rest) {
            if ($rest->end_time) {
                $restSeconds += Carbon::parse($rest->start_time)->diffInSeconds(Carbon::parse($rest->end_time));
            }
        }

        $workSeconds = $staySeconds - $restSeconds;
        $hours = floor($workSeconds / 3600);
        $minutes = floor(($workSeconds / 60) % 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }
}