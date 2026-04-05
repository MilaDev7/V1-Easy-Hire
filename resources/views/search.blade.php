@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4 fw-bold">Search Results</h3>

        @php
            $results = $professionals ?? collect();
        @endphp

        @if($results->isEmpty())
            <div id="noResults" class="text-center text-muted mt-5">
                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                <h5>No professionals found matching your search</h5>
                <p>Try searching for a different city or service.</p>
                <a href="/" class="btn btn-outline-dark mt-3">Back to Home</a>
            </div>
        @else
            <div id="results" class="row g-4">
                @foreach($results as $professional)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img
                                    src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                                    class="rounded-circle border"
                                    alt="{{ $professional->user->name ?? 'Verified Pro' }}"
                                    style="width:80px; height:80px; object-fit:cover;"
                                >
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $professional->user->name ?? 'Verified Pro' }}</h5>
                                    <p class="text-muted small mb-1">{{ $professional->skill ?: 'Professional' }}</p>
                                    <p class="text-muted small mb-0">
                                        <i class="fa-solid fa-location-dot me-1"></i>{{ $professional->location ?: 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button class="btn btn-outline-success w-100" onclick="handleProClick({{ $professional->id }})">
                                View Profile
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Modal for Unauthenticated Users -->
        <div class="modal fade" id="authModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4">
                    <div class="text-center">
                        <h5 class="fw-bold">Want to contact this pro?</h5>
                        <p class="text-muted">You need to register or login first.</p>
                        <div class="d-grid gap-2">
                            <a id="loginWithRedirect" href="/login" class="btn btn-success">Login Now</a>
                            <a href="/register" class="btn btn-outline-dark">Create Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Professional Profile Modal -->
    <div class="modal fade" id="pro-profile-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-tie me-2"></i>Professional Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="pro-profile-modal-body">
                    <div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
    
@section('scripts')
    <script>
        // Check for redirect from login and show profile
        document.addEventListener('DOMContentLoaded', function() {
            const proId = localStorage.getItem('pending_pro_id');
            if (proId) {
                localStorage.removeItem('pending_pro_id');
                showProProfile(proId);
            }
        });

        function handleProClick(professionalId) {
            const token = localStorage.getItem("token");
            
            if (token) {
                // User is logged in - show profile modal
                showProProfile(professionalId);
                return;
            }

            // Store the professional ID for after login
            localStorage.setItem('pending_pro_id', professionalId);
            
            // Show login modal with redirect
            const modalElement = document.getElementById('authModal');
            const loginLink = document.getElementById('loginWithRedirect');
            
            // Update login link to redirect back to this search page
            loginLink.href = '/login?redirect=' + encodeURIComponent(window.location.href);
            
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        function showProProfile(proId) {
            var modalBody = document.getElementById("pro-profile-modal-body");
            if (modalBody) {
                modalBody.innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';
            }
            
            var modalEl = document.getElementById("pro-profile-modal");
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }

            var token = localStorage.getItem("token");
            var headers = {"Accept": "application/json"};
            if (token) headers["Authorization"] = "Bearer " + token;

            fetch("/api/professionals/" + proId, {method: "GET", headers: headers})
            .then(function(r) { 
                if (!r.ok) throw new Error("Request failed"); 
                return r.json(); 
            })
            .then(function(data) {
                var pro = data.professional || {};
                var name = pro.user ? pro.user.name : (pro.name || "N/A");
                var email = pro.user ? pro.user.email : "N/A";
                var photo = pro.profile_photo ? "/storage/" + pro.profile_photo : "/images/user1.jpg";
                var skill = pro.skill || "N/A";
                var location = pro.location || "N/A";
                var experience = pro.experience || "0";
                var rating = data.average_rating || 0;
                var bio = pro.bio || "No biography available.";
                var completedJobs = data.completed_jobs ? data.completed_jobs.length : 0;
                var reviews = data.reviews || [];
                var reportsCount = data.reports_count || 0;

                function generateStars(r) {
                    var html = '';
                    var fullStars = Math.floor(r);
                    for (var i = 1; i <= 5; i++) {
                        html += '<i class="fa-solid fa-star" style="color: ' + (i <= fullStars ? '#ffc107;' : '#e4e5e9;') + '"></i>';
                    }
                    return html;
                }

                function formatDate(dateStr) {
                    if (!dateStr) return 'N/A';
                    var date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
                }

                var reviewsHtml = '';
                if (reviews.length > 0) {
                    reviews.forEach(function(review) {
                        reviewsHtml += '<div class="border-bottom pb-3 mb-3">' +
                            '<div class="d-flex justify-content-between mb-2">' +
                            '<strong><i class="fa-solid fa-user me-1"></i>' + (review.reviewer_name || 'Anonymous') + '</strong>' +
                            '<span>' + generateStars(review.rating) + '</span>' +
                            '</div>' +
                            '<p class="mb-1 text-muted small">' + (review.comment || 'No comment') + '</p>' +
                            '<small class="text-muted">' + formatDate(review.created_at) + '</small>' +
                            '</div>';
                    });
                } else {
                    reviewsHtml = '<div class="alert alert-light border mb-0"><i class="fa-solid fa-star me-2"></i>No reviews yet</div>';
                }

                var reportsHtml = '';
                if (reportsCount > 0) {
                    reportsHtml = '<div class="alert alert-danger py-2 mb-0"><i class="fa-solid fa-flag me-2"></i><strong>' + reportsCount + '</strong> report(s) filed against this professional</div>';
                } else {
                    reportsHtml = '<div class="alert alert-success py-2 mb-0"><i class="fa-solid fa-check-circle me-2"></i>No reports filed</div>';
                }

                if (modalBody) {
                    modalBody.innerHTML = `
                        <div class="text-center mb-4">
                            <img src="${photo}" alt="${name}" class="rounded-circle object-fit-cover border border-3 border-primary" style="width: 120px; height: 120px;">
                            <h3 class="mt-3 mb-1 fw-bold">${name}</h3>
                            <div class="mb-2">${generateStars(rating)} <span class="text-muted">(${rating.toFixed(1)})</span></div>
                            <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Verified Professional</span>
                        </div>
                        
                        ${reportsHtml}
                        
                        <div class="row g-3 mb-4 mt-3">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-info-circle me-2 text-primary"></i>Basic Info</h6>
                                        <p class="mb-2"><i class="fa-solid fa-code me-2 text-secondary"></i><strong>Skill:</strong> ${skill}</p>
                                        <p class="mb-2"><i class="fa-solid fa-location-dot me-2 text-secondary"></i><strong>Location:</strong> ${location}</p>
                                        <p class="mb-2"><i class="fa-solid fa-briefcase me-2 text-secondary"></i><strong>Experience:</strong> ${experience} years</p>
                                        <p class="mb-0"><i class="fa-solid fa-envelope me-2 text-secondary"></i><strong>Email:</strong> ${email}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-success"></i>Statistics</h6>
                                        <div class="text-center">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <h2 class="fw-bold text-success mb-0">${completedJobs}</h2>
                                                    <small class="text-muted">Completed Jobs</small>
                                                </div>
                                                <div class="col-6">
                                                    <h2 class="fw-bold text-warning mb-0">${data.reviews_count || 0}</h2>
                                                    <small class="text-muted">Reviews</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-user me-2 text-info"></i>About</h6>
                                <p class="mb-0" style="white-space: pre-wrap;">${bio}</p>
                            </div>
                        </div>
                        
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-star me-2 text-warning"></i>Reviews (${reviews.length})</h6>
                                ${reviewsHtml}
                            </div>
                        </div>
                    `;
                }
            })
            .catch(function(err) {
                console.error(err);
                if (modalBody) {
                    modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load profile.</div>';
                }
            });
        }
    </script>
@endsection
    
@section('scripts')
    <script>
        function handleProClick(professionalId) {
            if (localStorage.getItem("token")) {
                window.location.href = `/professionals/${professionalId}`;
                return;
            }

            const modalElement = document.getElementById('authModal');

            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }
    </script>
@endsection
