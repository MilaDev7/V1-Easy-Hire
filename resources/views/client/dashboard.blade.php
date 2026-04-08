<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body,
        .client-dashboard-main,
        .client-sidebar,
        .client-subscription-section,
        .client-subscription-section .card,
        .dashboard-stats .card,
        .job-post-list-section .card {
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        body.client-dashboard-dark {
            background: #111827 !important;
            color: #f9fafb;
        }

        body.client-dashboard-dark .client-sidebar,
        body.client-dashboard-dark .card,
        body.client-dashboard-dark .job-post-list-section .card,
        body.client-dashboard-dark .dashboard-stats .card {
            background: #1f2937 !important;
            color: #f9fafb !important;
        }

        body.client-dashboard-dark .text-muted,
        body.client-dashboard-dark .table,
        body.client-dashboard-dark .table th,
        body.client-dashboard-dark .table td,
        body.client-dashboard-dark .input-group-text,
        body.client-dashboard-dark .form-control,
        body.client-dashboard-dark .btn-outline-dark,
        body.client-dashboard-dark .btn-outline-secondary {
            color: #d1d5db !important;
        }

        body.client-dashboard-dark .form-control,
        body.client-dashboard-dark .input-group-text,
        body.client-dashboard-dark .table,
        body.client-dashboard-dark .table th,
        body.client-dashboard-dark .table td,
        body.client-dashboard-dark .btn-outline-dark,
        body.client-dashboard-dark .btn-outline-secondary {
            background: #111827 !important;
            border-color: #374151 !important;
        }

        body.client-dashboard-dark .table {
            --bs-table-bg: #1f2937;
            --bs-table-color: #f9fafb;
            --bs-table-border-color: #374151;
        }

        body.client-dashboard-dark .alert-light {
            background: #1f2937;
            color: #f9fafb;
            border-color: #374151;
        }

        body.client-dashboard-dark .modal-content,
        body.client-dashboard-dark .modal-header,
        body.client-dashboard-dark .modal-footer {
            background: #1f2937;
            color: #f9fafb;
            border-color: #374151;
        }

        body.client-dashboard-dark .btn-close {
            filter: invert(1) grayscale(100%);
        }
    </style>
</head>
<body class="bg-light">
    @include('client.components.leftnav')

    <div class="client-dashboard-main" style="margin-left: 280px; min-height: 100vh; padding: 24px 24px 140px;">
        <div class="card border-0 shadow-sm mb-4" style="background: #e7f1ff; border-top: 4px solid #0d6efd !important;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <p class="text-muted small mb-0">Client Dashboard</p>
                        <h1 class="h4 mb-0">Overview</h1>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('images/user1.jpg') }}" id="client-topbar-photo" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(0,0,0,0.1);">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/client/dashboard"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #2d665b, #1e4a42); border-top: 4px solid #28a745 !important;">
            <div class="card-body py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded p-2">
                            <i class="fa-solid fa-crown text-warning fs-4"></i>
                        </div>
                        <div>
                            <p class="text-white-50 small mb-0 text-uppercase">Current Plan</p>
                            <h5 class="text-white mb-0 fw-bold" id="current-plan-name">Loading...</h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4">
                        <div class="text-center" style="min-width: 80px;">
                            <p class="text-white-50 small mb-0 text-uppercase">Duration</p>
                            <p class="text-white mb-0 fw-semibold" id="current-plan-duration">--</p>
                        </div>
                        <div class="text-center" style="min-width: 100px;">
                            <p class="text-white-50 small mb-0 text-uppercase">Expires</p>
                            <p class="text-white mb-0 fw-semibold" id="current-plan-expiry">--</p>
                        </div>
                        <div class="text-center" style="min-width: 130px;">
                            <p class="text-white-50 small mb-0 text-uppercase">Direct Requests</p>
                            <p class="text-white mb-0 fw-semibold" id="current-plan-requests">--</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('client.components.stats')

        <div id="content-area">
            @include('client.components.jobposts-list')
        </div>
    </div>

    @include('client.components.subscription')

    <!-- Professional Profile Modal -->
    <div class="modal fade" id="pro-profile-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-tie me-2"></i>Professional Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="pro-profile-modal-body"></div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success d-none" id="hire-pro-btn" onclick="showDirectRequestModal()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Hire / Send Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Request Modal -->
    <div class="modal fade" id="direct-request-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-paper-plane me-2"></i>Send Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="direct-request-pro-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" class="form-control" id="direct-request-title" placeholder="e.g., Need web developer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="direct-request-desc" rows="4" placeholder="Describe your project..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Budget (Optional)</label>
                        <input type="number" class="form-control" id="direct-request-budget" placeholder="e.g., 500">
                    </div>
                    <div id="direct-request-feedback"></div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="send-direct-request-btn" onclick="sendDirectRequest()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Error Modal -->
    <div class="modal fade" id="application-error-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-exclamation me-2"></i>Cannot Accept</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-hourglass-half fa-4x text-warning mb-3"></i>
                    <p class="fs-5 mb-0" id="application-error-message"></p>
                </div>
                <div class="modal-footer border-top justify-content-center">
                    <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Job Confirmation Modal -->
    <div class="modal fade" id="delete-job-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Delete Job</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-trash fa-4x text-danger mb-3"></i>
                    <p class="fs-5">Are you sure you want to delete this job?</p>
                    <p class="text-muted" id="delete-job-title"></p>
                    <div id="delete-job-refund-notice" class="alert alert-info mb-0">
                        <i class="fa-solid fa-info-circle me-1"></i> You will get 1 post refunded since no one has applied.
                    </div>
                </div>
                <div class="modal-footer border-top justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirm-delete-job-btn">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/client.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/direct-request.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    
    <script>
        // Ensure variable is available globally
        var currentProIdForRequest = currentProIdForRequest || null;

        // Generate star rating HTML
        function generateStars(rating) {
            var html = '';
            var fullStars = Math.floor(rating);
            for (var i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    html += '<i class="fa-solid fa-star" style="color: #ffc107;"></i>';
                } else {
                    html += '<i class="fa-solid fa-star" style="color: #e4e5e9;"></i>';
                }
            }
            return html;
        }

        // Format date
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            var date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        }

        // Show professional profile modal
        function showProProfile(proId) {
            currentProIdForRequest = proId;
            
            var token = localStorage.getItem("token");
            var headers = {"Accept": "application/json"};
            if (token) headers["Authorization"] = "Bearer " + token;

            document.getElementById("pro-profile-modal-body").innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';
            var modal = new bootstrap.Modal(document.getElementById("pro-profile-modal"));
            modal.show();

            // Show/hide hire button based on login and role
            var hireBtn = document.getElementById("hire-pro-btn");
            var role = localStorage.getItem("role");
            if (hireBtn) {
                if (token && role === "client") {
                    hireBtn.classList.remove("d-none");
                } else {
                    hireBtn.classList.add("d-none");
                }
            }

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

                // Build reviews HTML
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

                // Reports warning
                var reportsHtml = '';
                if (reportsCount > 0) {
                    reportsHtml = '<div class="alert alert-danger py-2 mb-0"><i class="fa-solid fa-flag me-2"></i><strong>' + reportsCount + '</strong> report(s) filed against this professional</div>';
                } else {
                    reportsHtml = '<div class="alert alert-success py-2 mb-0"><i class="fa-solid fa-check-circle me-2"></i>No reports filed</div>';
                }

                document.getElementById("pro-profile-modal-body").innerHTML = `
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
            })
            .catch(function(err) {
                console.error(err);
                document.getElementById("pro-profile-modal-body").innerHTML = '<div class="alert alert-danger mb-0">Failed to load profile. Please try again.</div>';
            });
        }

        // Expose globally
        window.showProProfile = showProProfile;

        document.addEventListener("DOMContentLoaded", function () {
            if (typeof window.requireRole === "function") {
                window.requireRole("client");
            }
        });
    </script>
</body>
</html>
