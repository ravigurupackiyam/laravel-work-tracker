<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['work_type_id', 'description', 'day', 'month', 'year']);

        $query = TimeLog::with('workType')
            ->whereNotNull('end_time')
            ->when($filters['work_type_id'] ?? null, function ($q, $workTypeId) {
                $q->where('work_type_id', $workTypeId);
            })
            ->when($filters['description'] ?? null, function ($q, $description) {
                $q->where('description', 'like', '%' . $description . '%');
            })
            ->when($filters['day'] ?? null, function ($q, $day) {
                $q->whereDay('date', $day);
            })
            ->when($filters['month'] ?? null, function ($q, $month) {
                $q->whereMonth('date', $month);
            })
            ->when($filters['year'] ?? null, function ($q, $year) {
                $q->whereYear('date', $year);
            })
            ->orderByDesc('date')
            ->orderByDesc('start_time');

        // Sum total time across the *entire filtered set*, not just the current page
        $totalSeconds = (clone $query)->sum('total_seconds');

        $logs = $query->paginate(10)->withQueryString();

        // Is there a timer currently running? (started but not ended yet)
        $activeLog = TimeLog::with('workType')->whereNull('end_time')->latest('start_time')->first();

        $workTypes = WorkType::orderBy('name')->get();

        // Years available in the data, for the year filter dropdown
        $years = TimeLog::selectRaw('DISTINCT YEAR(date) as year')
            ->orderByDesc('year')
            ->pluck('year');

        return view('timelogs.index', compact('logs', 'activeLog', 'workTypes', 'filters', 'totalSeconds', 'years'));
    }

    // Called via AJAX when "Start Time" is clicked
    public function store(Request $request)
    {
        // Prevent starting a second timer while one is already running
        if (TimeLog::whereNull('end_time')->exists()) {
            return response()->json([
                'message' => 'A timer is already running. Please end it before starting a new one.',
            ], 422);
        }

        $validated = $request->validate([
            'work_type_id' => ['required', 'exists:work_types,id'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $now = Carbon::now();

        $timeLog = TimeLog::create([
            'work_type_id' => $validated['work_type_id'],
            'description' => $validated['description'] ?? null,
            'date' => $now->toDateString(),
            'start_time' => $now,
        ]);

        return response()->json([
            'id' => $timeLog->id,
            'start_time' => $timeLog->start_time->toIso8601String(),
        ]);
    }

    // Called via AJAX when "End Time" is clicked
    public function end(TimeLog $timeLog)
    {
        if ($timeLog->end_time) {
            return response()->json(['message' => 'This timer has already been ended.'], 422);
        }

        $now = Carbon::now();

        $timeLog->end_time = $now;
        $timeLog->total_seconds = $timeLog->start_time->diffInSeconds($now);
        $timeLog->save();

        return response()->json(['message' => 'Timer ended', 'redirect' => route('timelogs.index')]);
    }
}
