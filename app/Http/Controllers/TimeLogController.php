<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\WorkType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TimeLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['work_type_id', 'description', 'day', 'month', 'year']);

        $workTypesById = WorkType::all()->keyBy('id');

        $logs = TimeLog::all()
            ->filter(fn ($log) => !is_null($log->end_time))
            ->when($filters['work_type_id'] ?? null, fn ($c, $v) =>
                $c->filter(fn ($log) => (int) $log->work_type_id === (int) $v)
            )
            ->when($filters['description'] ?? null, fn ($c, $v) =>
                $c->filter(fn ($log) => str_contains(strtolower($log->description ?? ''), strtolower($v)))
            )
            ->when($filters['day'] ?? null, fn ($c, $v) =>
                $c->filter(fn ($log) => Carbon::parse($log->date)->day === (int) $v)
            )
            ->when($filters['month'] ?? null, fn ($c, $v) =>
                $c->filter(fn ($log) => Carbon::parse($log->date)->month === (int) $v)
            )
            ->when($filters['year'] ?? null, fn ($c, $v) =>
                $c->filter(fn ($log) => Carbon::parse($log->date)->year === (int) $v)
            )
            ->sortByDesc(fn ($log) => $log->start_time)
            ->values()
            ->map(function ($log) use ($workTypesById) {
                $log->workType = $workTypesById->get((int) $log->work_type_id);
                $log->date = Carbon::parse($log->date);
                $log->start_time = Carbon::parse($log->start_time);
                $log->end_time = $log->end_time ? Carbon::parse($log->end_time) : null;
                $log->total_time_formatted = TimeLog::formatSeconds($log->total_seconds ?? null);
                return $log;
            });

        $totalSeconds = $logs->sum('total_seconds');

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $paginatedLogs = new LengthAwarePaginator(
            $logs->forPage($page, $perPage)->values(),
            $logs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $activeLog = TimeLog::all()->first(fn ($log) => is_null($log->end_time));
        if ($activeLog) {
            $activeLog->workType = $workTypesById->get((int) $activeLog->work_type_id);
            $activeLog->start_time = Carbon::parse($activeLog->start_time);
        }

        $workTypes = $workTypesById->sortBy('name')->values();

        $years = TimeLog::all()
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('timelogs.index', [
            'logs' => $paginatedLogs,
            'activeLog' => $activeLog,
            'workTypes' => $workTypes,
            'filters' => $filters,
            'totalSeconds' => $totalSeconds,
            'years' => $years,
        ]);
    }

    // Called via AJAX when "Start Time" is clicked
    public function store(Request $request)
    {
        if (TimeLog::all()->contains(fn ($log) => is_null($log->end_time))) {
            return response()->json([
                'message' => 'A timer is already running. Please end it before starting a new one.',
            ], 422);
        }

        $validated = $request->validate([
            'work_type_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!WorkType::find($validated['work_type_id'])) {
            return response()->json(['message' => 'Selected work type does not exist.'], 422);
        }

        $now = Carbon::now();

        $timeLog = TimeLog::create([
            'work_type_id' => (int) $validated['work_type_id'],
            'description' => $validated['description'] ?? null,
            'date' => $now->toDateString(),
            'start_time' => $now->toDateTimeString(),
            'end_time' => null,
            'total_seconds' => null,
        ]);

        return response()->json([
            'id' => $timeLog->id,
            'start_time' => Carbon::parse($timeLog->start_time)->toIso8601String(),
        ]);
    }

    // Called via AJAX when "End Time" is clicked
    public function end(int $id)
    {
        $timeLog = TimeLog::find($id);

        if (!$timeLog) {
            return response()->json(['message' => 'Time log not found.'], 404);
        }

        if ($timeLog->end_time) {
            return response()->json(['message' => 'This timer has already been ended.'], 422);
        }

        $now = Carbon::now();
        $start = Carbon::parse($timeLog->start_time);

        TimeLog::updateRow($id, [
            'end_time' => $now->toDateTimeString(),
            'total_seconds' => $start->diffInSeconds($now),
        ]);

        return response()->json(['message' => 'Timer ended', 'redirect' => route('timelogs.index')]);
    }
}
