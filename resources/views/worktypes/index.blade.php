@extends('layouts.app')

@section('title', 'Manage Work Types')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-6 mx-lg-auto">
        <div class="glass-card">
            <div class="card-header-gradient p-3 p-md-4">
                <h5 class="mb-0"><i class="bi bi-tags"></i> Add Work Type</h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <form action="{{ route('worktypes.store') }}" method="POST" class="d-flex flex-column flex-sm-row gap-2">
                    @csrf
                    <input type="text" name="name" class="form-control form-control-lg rounded-3"
                           placeholder="e.g. Development, Meeting, Support" required maxlength="255">
                    <button type="submit" class="btn btn-gradient rounded-pill px-4">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </form>
                @error('name')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 mx-lg-auto">
        <div class="glass-card">
            <div class="card-header-gradient p-3 p-md-4">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Existing Work Types</h5>
            </div>
            <div class="card-body p-2 p-md-3">
                <ul class="list-group list-group-flush">
                    @forelse ($workTypes as $type)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                            <div>
                                <span class="badge-worktype">{{ $type->name }}</span>
                                <span class="text-muted small ms-2">{{ $type->time_logs_count }} log(s)</span>
                            </div>
                            <form action="{{ route('worktypes.destroy', $type) }}" method="POST"
                                  onsubmit="return confirm('Delete this work type?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                        {{ $type->time_logs_count > 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item bg-transparent text-center text-muted py-4">
                            No work types yet — add one above.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('timelogs.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-arrow-left"></i> Back to Timer
            </a>
        </div>
    </div>
</div>
@endsection
