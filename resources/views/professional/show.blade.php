@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-dark btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row gap-4 align-items-md-start">
                <img
                    src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                    alt="{{ $professional->user->name ?? 'Verified Pro' }}"
                    class="rounded-circle object-fit-cover border"
                    style="width: 108px; height: 108px;"
                >

                <div class="flex-grow-1">
                    <div class="mb-4">
                        <h1 class="h3 fw-bold mb-1">{{ $professional->user->name ?? 'Verified Pro' }}</h1>
                        <p class="text-muted mb-0">{{ $professional->skill ?: 'Professional' }}</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="text-muted small mb-1">Location</p>
                                <p class="mb-0 fw-semibold">{{ $professional->location ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="text-muted small mb-1">Experience</p>
                                <p class="mb-0 fw-semibold">{{ $professional->experience ?? 'N/A' }} years</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <p class="text-muted small mb-1">Status</p>
                                <p class="mb-0 fw-semibold text-capitalize">{{ $professional->status ?: 'active' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-muted small mb-1">Bio</p>
                        <p class="mb-0">{{ $professional->bio ?: 'No bio provided.' }}</p>
                    </div>

                    <div>
                        <p class="text-muted small mb-2">Completed Jobs</p>
                        <div class="border rounded-4 overflow-hidden">
                            @if($completedJobs->isEmpty())
                                <div class="p-3">No completed jobs found.</div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($completedJobs as $job)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ $job->job->title ?? 'Completed Job' }}</span>
                                            <span class="badge text-bg-light border">{{ $job->job->status ?? $job->status ?? 'completed' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
