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
        return self::formatSeconds($this->total_seconds);
    }

    // Reusable formatter, e.g. for the grand-total row under the table
    public static function formatSeconds(?int $seconds): string
    {
        if (is_null($seconds)) {
            return '-';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
