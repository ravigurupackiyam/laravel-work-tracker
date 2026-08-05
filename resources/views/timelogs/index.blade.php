@extends('layouts.app')

@section('title', 'Work Timer')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="card-header-gradient p-3 p-md-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Time Log</h5>
                <span class="badge bg-light text-dark rounded-pill px-3 py-2">{{ $logs->total() }} entries</span>
            </div>
            <div class="card-body p-2 p-md-4">

                <!-- Filters -->
                <form action="{{ route('timelogs.index') }}" method="GET" class="row g-2 g-md-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Work Type</label>
                        <select name="work_type_id" class="form-select rounded-3">
                            <option value="">All</option>
                            @foreach ($workTypes as $type)
                                <option value="{{ $type->id }}" @selected(($filters['work_type_id'] ?? '') == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Description</label>
                        <input type="text" name="description" value="{{ $filters['description'] ?? '' }}"
                               class="form-control rounded-3" placeholder="Search...">
                    </div>

                    <div class="col-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Day</label>
                        <select name="day" class="form-select rounded-3">
                            <option value="">Any</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" @selected(($filters['day'] ?? '') == $d)>{{ $d }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Month</label>
                        <select name="month" class="form-select rounded-3">
                            <option value="">Any</option>
                            @foreach (['1'=>'Jan','2'=>'Feb','3'=>'Mar','4'=>'Apr','5'=>'May','6'=>'Jun','7'=>'Jul','8'=>'Aug','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'] as $num => $name)
                                <option value="{{ $num }}" @selected(($filters['month'] ?? '') == $num)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4 col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Year</label>
                        <select name="year" class="form-select rounded-3">
                            <option value="">Any</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-between">
                        <a href="{{ route('timelogs.manually') }}" class="btn btn-gradient rounded-pill px-4">Add</a>
                        <button type="submit" class="btn btn-gradient rounded-pill px-4">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        @if (array_filter($filters))
                            <a href="{{ route('timelogs.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="bi bi-x-lg"></i> Clear
                            </a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-head-brand">
                            <tr>
                                <th>S.No</th>
                                <th style="min-width: 125px;">Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th style="min-width: 225px;">Work Type</th>
                                <th>Description</th>
                                <th>Total Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $logs->firstItem() + $loop->index }}</td>
                                    <td style="min-width: 125px;">{{ $log->date->format('d M Y') }}</td>
                                    <td>{{ $log->start_time->format('h:i A') }}</td>
                                    <td>{{ $log->end_time?->format('h:i A') ?? '-' }}</td>
                                    <td style="min-width: 225px;"><span class="badge-worktype">{{ $log->workType->name }}</span></td>
                                    <td class="text-muted">{{ $log->description ?: '-' }}</td>
                                    <td class="fw-semibold">{{ $log->total_time_formatted }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        No time logs match these filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->isNotEmpty())
                    <div class="d-flex justify-content-end mt-3">
                        <div class="px-4 py-2 rounded-pill" style="background: #f5f3ff;">
                            <span class="text-muted small fw-semibold me-2">Total Time (filtered):</span>
                            <span class="fw-bold text-primary">{{ \App\Models\TimeLog::formatSeconds($totalSeconds) }}</span>
                        </div>
                    </div>
                @endif

                <div class="mt-4 d-flex justify-content-center">
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8 mx-lg-auto">
        <div class="glass-card"
             x-data="timerApp({
                 initialRunning: {{ $activeLog ? 'true' : 'false' }},
                 initialLogId: {{ $activeLog->id ?? 'null' }},
                 initialStartTime: {{ $activeLog ? "'" . $activeLog->start_time->toIso8601String() . "'" : 'null' }},
                 initialWorkTypeId: {{ $activeLog->work_type_id ?? 'null' }},
                 initialDescription: {{ $activeLog ? json_encode($activeLog->description) : 'null' }}
             })"
             x-init="init()">

            <div class="card-header-gradient p-3 p-md-4">
                <h5 class="mb-0"><i class="bi bi-play-circle"></i> Log New Work</h5>
            </div>

            <div class="card-body p-3 p-md-4">

                <!-- Timer display, shown once running -->
                <div class="text-center mb-4" x-show="running" x-cloak>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <span class="pulse-dot"></span>
                        <span class="text-uppercase small text-muted fw-semibold">Timer running</span>
                    </div>
                    <div class="timer-display" x-text="formattedTimer"></div>
                </div>

                <div x-show="errorMessage" x-cloak class="alert alert-danger rounded-4" x-text="errorMessage"></div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Work Type <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg rounded-3"
                                x-model="workTypeId"
                                :disabled="running">
                            <option value="" disabled selected>Select work type</option>
                            @foreach ($workTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @if ($workTypes->isEmpty())
                            <div class="form-text text-danger">
                                No work types yet — <a href="{{ route('worktypes.index') }}">add one first</a>.
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Description <span class="text-muted small">(optional)</span></label>
                        <textarea class="form-control rounded-3" rows="1" style="resize: vertical;"
                                  x-model="description"
                                  :disabled="running"
                                  placeholder="What are you working on?"></textarea>
                    </div>
                </div>

                <div class="d-grid d-md-flex justify-content-md-center mt-4 gap-3">
                    <button type="button"
                            class="btn btn-gradient btn-lg rounded-pill px-5 py-3"
                            x-show="!running" x-cloak
                            :disabled="!workTypeId || submitting"
                            @click="start()">
                        <i class="bi bi-play-fill"></i> Start Time
                    </button>

                    <button type="button"
                            class="btn btn-end btn-lg rounded-pill px-5 py-3"
                            x-show="running" x-cloak
                            :disabled="submitting"
                            @click="end()">
                        <i class="bi bi-stop-fill"></i> End Time
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function timerApp(config) {
    return {
        workTypeId: config.initialWorkTypeId ?? '',
        description: config.initialDescription ?? '',
        running: config.initialRunning,
        logId: config.initialLogId,
        startTime: config.initialStartTime ? new Date(config.initialStartTime) : null,
        elapsedSeconds: 0,
        submitting: false,
        errorMessage: '',
        intervalId: null,

        init() {
            if (this.running && this.startTime) {
                this.tick();
                this.intervalId = setInterval(() => this.tick(), 1000);
            }
        },

        tick() {
            const now = new Date();
            this.elapsedSeconds = Math.max(0, Math.floor((now - this.startTime) / 1000));
        },

        get formattedTimer() {
            const h = Math.floor(this.elapsedSeconds / 3600).toString().padStart(2, '0');
            const m = Math.floor((this.elapsedSeconds % 3600) / 60).toString().padStart(2, '0');
            const s = (this.elapsedSeconds % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },

        async start() {
            if (!this.workTypeId) return;
            this.submitting = true;
            this.errorMessage = '';

            try {
                const res = await fetch('{{ route('timelogs.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({
                        work_type_id: this.workTypeId,
                        description: this.description,
                    }),
                });

                const data = await res.json();

                if (!res.ok) {
                    this.errorMessage = data.message || 'Could not start the timer.';
                    this.submitting = false;
                    return;
                }

                this.logId = data.id;
                this.startTime = new Date(data.start_time);
                this.elapsedSeconds = 0;
                this.running = true;
                this.intervalId = setInterval(() => this.tick(), 1000);
            } catch (e) {
                this.errorMessage = 'Network error — please try again.';
            } finally {
                this.submitting = false;
            }
        },

        async end() {
            if (!this.logId) return;
            this.submitting = true;
            this.errorMessage = '';

            try {
                const res = await fetch(`/timelogs/${this.logId}/end`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                });

                const data = await res.json();

                if (!res.ok) {
                    this.errorMessage = data.message || 'Could not end the timer.';
                    this.submitting = false;
                    return;
                }

                clearInterval(this.intervalId);
                window.location.reload();
            } catch (e) {
                this.errorMessage = 'Network error — please try again.';
                this.submitting = false;
            }
        },
    };
}
</script>
@endsection
