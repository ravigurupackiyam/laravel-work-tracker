<?php

use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\WorkTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimeLogController::class, 'index'])->name('timelogs.index');
Route::post('/timelogs', [TimeLogController::class, 'store'])->name('timelogs.store');
Route::post('/timelogs/{timeLog}/end', [TimeLogController::class, 'end'])->name('timelogs.end');

Route::get('/work-types', [WorkTypeController::class, 'index'])->name('worktypes.index');
Route::post('/work-types', [WorkTypeController::class, 'store'])->name('worktypes.store');
Route::delete('/work-types/{workType}', [WorkTypeController::class, 'destroy'])->name('worktypes.destroy');

Route::get('/add', [TimeLogController::class, 'manuallyAddShow'])->name('timelogs.manually');
Route::post('/add-data', [TimeLogController::class, 'manuallyAdd'])->name('timelogs.manually.add');
