@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card border-0 shadow-sm rounded-4" style="border-top: 5px solid #6f42c1 !important;">
        <div class="row g-0">
            <div class="col-md-4 text-center p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <img
                    src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                    class="rounded-circle border border-3 mb-3"
                    alt="{{ $professional->user->name ?? 'Professional' }}"
                    style="width: 150px; height: 150px; object-fit: cover; border-color: #6f42c1 !important;"
                >
                <h3 class="fw-bold text-dark">{{ $professional->user->name ?? 'Professional' }}</h3>
                <span class="badge bg-primary mb-2">{{ $professional->skill ?: 'Professional' }}</span>
                <p class="text-muted mb-1">
                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                    {{ $professional->location ?: 'N/A' }}
                </p>
                <div class="mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-star {{ $i <= round($professional->average_rating ?? 0) ? 'fas text-warning' : 'far text-secondary' }}"></i>
                    @endfor
                    @if(($professional->average_rating ?? 0) > 0)
                        <span class="text-muted small">({{ number_format($professional->average_rating ?? 0, 1) }})</span>
                    @else
                        <span class="text-muted small">(No ratings)</span>
                    @endif
                </div>
            </div>
            <div class="col-md-8 p-4">
                <h4 class="fw-bold text-primary mb-3">
                    <i class="fa-solid fa-user me-2"></i>About
                </h4>
                <p class="text-dark mb-4 bg-light p-3 rounded">{{ $professional->bio ?: 'No bio available.' }}</p>
                
                <h4 class="fw-bold text-success mb-3">
                    <i class="fa-solid fa-briefcase me-2"></i>Experience
                </h4>
                <div class="alert alert-success mb-4">
                    <strong>{{ $professional->years_of_experience ?? 0 }} years</strong> of professional experience
                </div>
                
                <h4 class="fw-bold text-warning mb-3">
                    <i class="fa-solid fa-check-circle me-2"></i>Completed Jobs
                </h4>
                @if($completedJobs->isEmpty())
                    <div class="alert alert-secondary">No completed jobs yet.</div>
                @else
                    <div class="list-group">
                        @foreach($completedJobs as $application)
                            <div class="list-group-item border-success mb-2 rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-success">{{ $application->job->title ?? 'Job' }}</strong>
                                        <span class="badge bg-success ms-2">Completed</span>
                                    </div>
                                    <small class="text-muted">{{ $application->job->created_at ?? '' }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
        <div class="p-4 text-center bg-light rounded-bottom">
            <a href="/" class="btn btn-outline-primary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
