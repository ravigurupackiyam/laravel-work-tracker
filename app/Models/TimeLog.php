<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_type_id',
        'description',
        'date',
        'start_time',
        'end_time',
        'total_seconds',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function isRunning(): bool
    {
        return is_null($this->end_time);
    }

    // Formatted "HH:MM:SS" for the Total Time column
    public function getTotalTimeFormattedAttribute(): string
    {
        if (is_null($this->total_seconds)) {
            return '-';
        }

        $hours = intdiv($this->total_seconds, 3600);
        $minutes = intdiv($this->total_seconds % 3600, 60);
        $seconds = $this->total_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
