<?php

namespace App\Models;

use App\Support\JsonModel;

class TimeLog extends JsonModel
{
    protected static string $file = 'time_logs.json';

    // Reusable "HH:MM:SS" formatter, used both per-row and for the grand-total pill
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
