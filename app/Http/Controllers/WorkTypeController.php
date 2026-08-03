<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkType;

class WorkTypeController extends Controller
{
    public function index()
    {
        $workTypes = WorkType::withCount('timeLogs')->orderBy('name')->get();

        return view('worktypes.index', compact('workTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:work_types,name'],
        ]);

        WorkType::create($validated);

        return redirect()->route('worktypes.index')->with('success', 'Work type added.');
    }

    public function destroy(WorkType $workType)
    {
        if ($workType->timeLogs()->exists()) {
            return redirect()->route('worktypes.index')
                ->with('error', 'Cannot delete a work type that already has time logs.');
        }

        $workType->delete();

        return redirect()->route('worktypes.index')->with('success', 'Work type removed.');
    }
}
