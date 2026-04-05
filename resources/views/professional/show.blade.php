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
                
                @if($reportsCount > 0)
                    <div class="alert alert-danger py-1 px-2 mb-2 small">
                        <i class="fa-solid fa-flag me-1"></i><strong>{{ $reportsCount }}</strong> report(s)
                    </div>
                @else
                    <div class="alert alert-success py-1 px-2 mb-2 small">
                        <i class="fa-solid fa-check-circle me-1"></i>No reports
                    </div>
                @endif
                
                <div class="d-flex justify-content-center gap-3 mt-2">
                    <div class="text-center">
                        <strong class="text-success fs-5">{{ $completedJobs->count() }}</strong>
                        <small class="d-block text-muted">Jobs</small>
                    </div>
                    <div class="text-center">
                        <strong class="text-warning fs-5">{{ $reviews->count() }}</strong>
                        <small class="d-block text-muted">Reviews</small>
                    </div>
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
                    <strong>{{ $professional->experience ?? 0 }} years</strong> of professional experience
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
                
                <h4 class="fw-bold text-info mb-3 mt-4">
                    <i class="fa-solid fa-star me-2"></i>Reviews ({{ $reviews->count() }})
                </h4>
                @if($reviews->isEmpty())
                    <div class="alert alert-secondary">No reviews yet.</div>
                @else
                    <div class="list-group">
                        @foreach($reviews as $review)
                            <div class="list-group-item mb-2 rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <i class="fa-solid fa-user me-1 text-primary"></i>
                                        <strong>{{ $review->reviewer->name ?? 'Anonymous' }}</strong>
                                    </div>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-star {{ $i <= $review->rating ? 'fas text-warning' : 'far text-secondary' }}" style="font-size: 12px;"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-1 text-muted small">{{ $review->comment ?: 'No comment' }}</p>
                                <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
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
