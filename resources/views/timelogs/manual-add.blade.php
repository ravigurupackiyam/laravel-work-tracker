@extends('layouts.app')

@section('title', 'Work Timer')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-8 mx-lg-auto">
            <div class="card-header-gradient p-3 p-md-4">
                <h5 class="mb-0"><i class="bi bi-play-circle"></i> Add Manual Log</h5>
            </div>
            <form method="POST" action="{{ route('timelogs.manually.add') }}">
                @csrf
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Work Type <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg rounded-3" name="work_type_id" x-model="workTypeId"
                                :disabled="running">
                                <option value="" disabled selected>Select work type</option>
                                @foreach ($workTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Description <span
                                    class="text-muted small">(optional)</span></label>
                            <textarea class="form-control rounded-3" rows="1" style="resize: vertical;" name="description" x-model="description"
                                :disabled="running" placeholder="What are you working on?"></textarea>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                Start Time <span class="text-danger">*</span>
                            </label>

                            <input type="datetime-local" id="start_time" name="start_time"
                                class="form-control form-control-lg rounded-3">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                End Time <span class="text-danger">*</span>
                            </label>

                            <input type="datetime-local" id="end_time" name="end_time"
                                class="form-control form-control-lg rounded-3">
                        </div>
                    </div>
                    <div class="d-grid d-md-flex justify-content-md-center mt-4 gap-3">
                        <button type="submit" class="btn btn-gradient btn-lg rounded-pill px-5 py-3">
                            <i class="bi bi-play-fill"></i> Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
