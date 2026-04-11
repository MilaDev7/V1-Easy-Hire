@php
    $skillParts = collect(preg_split('/[,\/|]+/', (string) ($professional->skill ?? '')))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    if ($skillParts->isEmpty() && !empty($professional->skill)) {
        $skillParts = collect([trim((string) $professional->skill)]);
    }

    $status = strtolower((string) ($professional->status ?? 'pending'));
    $statusLabel = ucfirst($status);
    $statusClass = $status === 'approved'
        ? 'bg-success-subtle text-success'
        : ($status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border-top: 5px solid #198754 !important;">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-auto text-center">
                        <img
                            src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                            class="rounded-circle border border-3 shadow-sm"
                            alt="{{ $professional->user->name ?? 'Professional' }}"
                            style="width: 130px; height: 130px; object-fit: cover;"
                        >
                    </div>
                    <div class="col">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <h2 class="h3 fw-bold mb-0 text-dark">{{ $professional->user->name ?? 'Professional' }}</h2>
                            <span class="badge rounded-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if(($reportsCount ?? 0) > 3)
                                <span class="badge rounded-pill text-bg-danger">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    This professional has received multiple reports
                                </span>
                            @endif
                        </div>

                        <p class="text-muted mb-2">{{ $professional->skill ?: 'Professional' }}</p>

                        <div class="d-flex flex-wrap align-items-center gap-3 small mb-3">
                            <span class="d-inline-flex align-items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-star {{ $i <= round($averageRating ?? 0) ? 'fas text-warning' : 'far text-secondary' }}"></i>
                                @endfor
                                <span class="ms-2 text-muted">
                                    {{ number_format((float) ($averageRating ?? 0), 1) }} ({{ ($reviews ?? collect())->count() }} reviews)
                                </span>
                            </span>
                            <span class="text-muted">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $professional->location ?: 'N/A' }}
                            </span>
                        </div>

                        <div id="profile-actions-client" class="d-none">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-success" id="action-hire-btn">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="action-report-btn">
                                    <i class="fa-solid fa-flag me-1"></i> Report
                                </button>
                            </div>
                        </div>

                        <div id="profile-actions-guest" class="alert alert-info d-none py-2 px-3 mb-0 mt-2">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Login as a client to send a request or report.
                            <a href="/login" class="alert-link ms-1">Login</a>
                        </div>

                        <div id="profile-actions-admin" class="alert alert-warning d-none py-2 px-3 mb-0 mt-2">
                            <i class="fa-solid fa-shield-halved me-1"></i>
                            Admin view: moderation mode enabled.
                            <a href="/admin/dashboard" class="alert-link ms-1">Open Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h4 class="fw-bold text-primary mb-3">
                    <i class="fa-solid fa-user me-2"></i>About
                </h4>
                <p class="text-dark bg-light rounded-3 p-3 mb-3">{{ $professional->bio ?: 'No bio available.' }}</p>
                <div class="alert alert-success mb-0">
                    <i class="fa-solid fa-briefcase me-2"></i>
                    <strong>{{ $professional->experience ?? 0 }} years</strong> experience
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h4 class="fw-bold text-dark mb-3">
                    <i class="fa-solid fa-tags me-2"></i>Skills
                </h4>
                @if($skillParts->isEmpty())
                    <div class="alert alert-secondary mb-0">No skills listed.</div>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($skillParts as $skill)
                            <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-success mb-3">
                    <i class="fa-solid fa-layer-group me-2"></i>Portfolio
                </h4>
                @if(($completedJobs ?? collect())->isEmpty())
                    <div class="alert alert-secondary mb-0">No project portfolio available yet.</div>
                @else
                    <div class="row g-3">
                        @foreach($completedJobs as $application)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card border h-100 rounded-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="fw-bold mb-0 text-success">{{ $application->job->title ?? 'Project' }}</h6>
                                            <span class="badge bg-success">Completed</span>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            {{ \Illuminate\Support\Str::limit((string) ($application->job->description ?? 'No description available.'), 100) }}
                                        </p>
                                        <div class="small text-muted d-flex justify-content-between">
                                            <span><i class="fa-solid fa-location-dot me-1"></i>{{ $application->job->location ?? 'N/A' }}</span>
                                            <span>{{ optional($application->job->created_at)->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12" id="profile-reviews-section">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-info mb-3">
                    <i class="fa-solid fa-star me-2"></i>Reviews ({{ ($reviews ?? collect())->count() }})
                </h4>
                @if(($reviews ?? collect())->isEmpty())
                    <div class="alert alert-secondary mb-0">No reviews yet.</div>
                @else
                    <div class="list-group">
                        @foreach($reviews as $review)
                            <div class="list-group-item border-0 rounded-3 mb-2 bg-light {{ $loop->index >= 3 ? 'd-none profile-review-extra' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <i class="fa-solid fa-user me-1 text-primary"></i>
                                        <strong>{{ $review->reviewer->name ?? $review->reviewer_name ?? 'Anonymous' }}</strong>
                                    </div>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-star {{ $i <= (int) ($review->rating ?? 0) ? 'fas text-warning' : 'far text-secondary' }}" style="font-size: 12px;"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-1 text-muted small">{{ $review->comment ?: 'No comment' }}</p>
                                <small class="text-muted">{{ optional($review->created_at)->format('M d, Y') }}</small>
                            </div>
                        @endforeach
                    </div>
                    @if(($reviews ?? collect())->count() > 3)
                        <div class="text-center mt-3">
                            <button type="button" id="profile-show-more-reviews-btn" class="btn btn-outline-info btn-sm">
                                <i class="fa-solid fa-angles-down me-1"></i>See more reviews
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-12" id="profile-reports-section">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold text-danger mb-3">
                    <i class="fa-solid fa-shield-halved me-2"></i>System Signal
                </h4>
                <div class="alert {{ ($reportsCount ?? 0) > 3 ? 'alert-danger' : 'alert-success' }} mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span>
                        <i class="fa-solid fa-flag me-2"></i>
                        Report Count: <strong>{{ (int) ($reportsCount ?? 0) }}</strong>
                    </span>
                    @if(($reportsCount ?? 0) > 3)
                        <span class="badge text-bg-danger">This professional has received multiple reports</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
