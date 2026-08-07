<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\WorkType;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    public function index()
    {
        $counts = TimeLog::all()->countBy(fn ($log) => (int) $log->work_type_id);

        $workTypes = WorkType::all()
            ->sortBy('name')
            ->values()
            ->map(function ($type) use ($counts) {
                $type->time_logs_count = $counts->get((int) $type->id, 0);
                return $type;
            });

        return view('worktypes.index', compact('workTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $alreadyExists = WorkType::all()
            ->contains(fn ($type) => strcasecmp($type->name, $validated['name']) === 0);

        if ($alreadyExists) {
            return back()->withErrors(['name' => 'This work type already exists.'])->withInput();
        }

        WorkType::create(['name' => $validated['name']]);

        return redirect()->route('worktypes.index')->with('success', 'Work type added.');
    }

    public function destroy(int $id)
    {
        $hasLogs = TimeLog::all()->contains(fn ($log) => (int) $log->work_type_id === $id);

        if ($hasLogs) {
            return redirect()->route('worktypes.index')
                ->with('error', 'Cannot delete a work type that already has time logs.');
        }

        WorkType::deleteRow($id);

        return redirect()->route('worktypes.index')->with('success', 'Work type removed.');
    }
}
