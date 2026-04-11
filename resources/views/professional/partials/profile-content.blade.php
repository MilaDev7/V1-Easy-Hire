<div class="card border-0 shadow-sm rounded-4" style="border-top: 5px solid #198754 !important;">
    <div class="row g-0">
        <div class="col-md-4 text-center p-4 bg-light">
            <img
                src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                class="rounded-circle border border-3 mb-3"
                alt="{{ $professional->user->name ?? 'Professional' }}"
                style="width: 150px; height: 150px; object-fit: cover;"
            >
            <h3 class="fw-bold text-dark">{{ $professional->user->name ?? 'Professional' }}</h3>
            <span class="badge bg-success mb-2">{{ $professional->skill ?: 'Professional' }}</span>
            <p class="text-muted mb-1">
                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                {{ $professional->location ?: 'N/A' }}
            </p>
            <div class="mb-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fa-star {{ $i <= round($averageRating ?? 0) ? 'fas text-warning' : 'far text-secondary' }}"></i>
                @endfor
                @if(($averageRating ?? 0) > 0)
                    <span class="text-muted small">({{ number_format($averageRating ?? 0, 1) }})</span>
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
                    <small class="d-block text-muted">Completed Jobs</small>
                </div>
                <div class="text-center">
                    <strong class="text-warning fs-5">{{ $reviews->count() }}</strong>
                    <small class="d-block text-muted">Reviews</small>
                </div>
            </div>
        </div>

        <div class="col-md-8 p-4">
            <div id="profile-actions-client" class="d-none mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-success" id="action-hire-btn">
                        <i class="fa-solid fa-briefcase me-1"></i> Hire
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="action-review-btn">
                        <i class="fa-solid fa-star me-1"></i> Review
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="action-report-btn">
                        <i class="fa-solid fa-flag me-1"></i> Report
                    </button>
                </div>
            </div>

            <div id="profile-actions-guest" class="alert alert-info d-none">
                <i class="fa-solid fa-circle-info me-1"></i>
                Login as a client to hire or send a direct request.
                <a href="/login" class="alert-link ms-1">Login</a>
            </div>

            <div id="profile-actions-admin" class="alert alert-warning d-none">
                <i class="fa-solid fa-shield-halved me-1"></i>
                Admin view: moderation mode enabled.
                <a href="/admin/dashboard" class="alert-link ms-1">Open Dashboard</a>
            </div>

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
                                <small class="text-muted">{{ optional($application->job->created_at)->format('M d, Y') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div id="profile-reviews-section">
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
                                <small class="text-muted">{{ optional($review->created_at)->format('M d, Y') }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div id="profile-reports-section">
                <h4 class="fw-bold text-danger mb-3 mt-4">
                    <i class="fa-solid fa-flag me-2"></i>Reports ({{ $reportsCount ?? 0 }})
                </h4>
                @if(($reportsCount ?? 0) > 0)
                    <div class="alert alert-danger d-flex justify-content-between align-items-center mb-0">
                        <span>
                            <i class="fa-solid fa-flag me-2"></i>
                            Total reports: <strong>{{ (int) ($reportsCount ?? 0) }}</strong>
                        </span>
                        @if(($reportsCount ?? 0) > 3)
                            <span class="badge text-bg-danger">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Warning
                            </span>
                        @endif
                    </div>
                @else
                    <div class="alert alert-success mb-0">
                        <i class="fa-solid fa-check-circle me-2"></i>No reports
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
