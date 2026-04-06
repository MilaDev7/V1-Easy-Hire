<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 28%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: #0f172a;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .professional-dashboard-shell {
            min-height: 100vh;
            padding: 24px 24px 140px;
            margin-left: 300px;
        }

        .professional-dashboard-container {
            max-width: none;
            margin: 0;
        }

        .professional-sidebar,
        .professional-content-card,
        .pro-stat-card,
        .professional-job-card,
        .professional-settings-modal .modal-content {
            border: 1px solid rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .professional-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 300px;
            min-height: 100vh;
            border-radius: 0 28px 28px 0;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-top: 4px solid #6f42c1;
        }

        .professional-sidebar-photo {
            width: 112px;
            height: 112px;
            object-fit: cover;
        }

        .professional-sidebar-meta {
            border-radius: 18px;
            background: #f8fafc;
        }

        .professional-sidebar-nav .btn {
            border-radius: 16px;
            padding: 0.9rem 1rem;
            font-weight: 600;
        }

        .professional-sidebar-nav .btn i {
            width: 18px;
        }

        .professional-content-card,
        .pro-stat-card,
        .professional-job-card {
            border-radius: 20px;
        }

        .professional-job-meta {
            font-size: 0.95rem;
        }

        .professional-job-description {
            color: #475569;
        }

        .professional-job-skills {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.875rem;
            font-weight: 600;
        }

        body.professional-dashboard-dark {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 28%),
                linear-gradient(180deg, #0f172a 0%, #111827 100%);
            color: #e5eefb;
        }

        body.professional-dashboard-dark .professional-sidebar,
        body.professional-dashboard-dark .professional-content-card,
        body.professional-dashboard-dark .pro-stat-card,
        body.professional-dashboard-dark .professional-job-card,
        body.professional-dashboard-dark .professional-settings-modal .modal-content,
        body.professional-dashboard-dark .professional-settings-modal .modal-header,
        body.professional-dashboard-dark .professional-settings-modal .modal-footer {
            background: #162033 !important;
            color: #e5eefb !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        body.professional-dashboard-dark .professional-sidebar-meta,
        body.professional-dashboard-dark .professional-job-skills,
        body.professional-dashboard-dark .badge.text-bg-light,
        body.professional-dashboard-dark .alert-light,
        body.professional-dashboard-dark .list-group-item,
        body.professional-dashboard-dark .form-control,
        body.professional-dashboard-dark .form-select {
            background: #0f172a !important;
            color: #e5eefb !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        body.professional-dashboard-dark .text-muted {
            color: #94a3b8 !important;
        }

        body.professional-dashboard-dark .btn-close {
            filter: invert(1) grayscale(100%);
        }

        @media (max-width: 991.98px) {
            .professional-dashboard-grid {
                grid-template-columns: 1fr;
            }

            .professional-sidebar {
                position: static;
                width: 100%;
                border-radius: 0;
            }

            .professional-dashboard-shell {
                margin-left: 0;
                padding: 24px 16px 64px;
            }
        }

        .professional-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
            margin-left: 300px;
        }

        .professional-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-grow: 1;
        }

        .professional-topbar-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(15, 23, 42, 0.12);
        }

        .professional-topbar-status {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
        }

        .professional-topbar-status.approved {
            background: #dcfce7;
            color: #166534;
        }

        .professional-topbar-status.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .professional-topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .professional-topbar .btn-logout {
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        body.professional-dashboard-dark .professional-topbar {
            background: #162033 !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        body.professional-dashboard-dark .professional-topbar-photo {
            border-color: rgba(148, 163, 184, 0.3);
        }

        body.professional-dashboard-dark .professional-topbar-status.approved {
            background: rgba(22, 101, 52, 0.3);
            color: #86efac;
        }

        body.professional-dashboard-dark .professional-topbar-status.pending {
            background: rgba(146, 64, 14, 0.3);
            color: #fcd34d;
        }
    </style>
</head>
<body class="bg-light">
    <main class="professional-dashboard-shell" id="professional-dashboard">
        <div class="card border-0 shadow-sm mb-4 position-relative" style="background: #e6fffa; border-top: 4px solid #20c997 !important;">
            <div class="card-body text-center py-4">
                <div>
                    <p class="text-muted small mb-0">Professional Dashboard</p>
                    <h1 class="h4 mb-0">Overview</h1>
                </div>

                <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 me-4 top-50 translate-middle-y" onclick="logout()">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </button>
            </div>
        </div>

        <div class="professional-dashboard-container">
            <div class="professional-dashboard-grid">
                <aside class="professional-sidebar shadow-sm p-4 d-flex flex-column">
                    <div class="text-center mb-4">
                        <img
                            src="{{ asset('images/user1.jpg') }}"
                            alt="Professional Photo"
                            id="professional-dashboard-photo"
                            class="professional-sidebar-photo rounded-circle border mb-3"
                        >
                        <h1 class="h4 mb-1" id="professional-dashboard-name">Professional</h1>
                        <p class="text-muted mb-2" id="professional-sidebar-skill">Skill</p>
                        <span class="badge rounded-pill text-bg-warning" id="professional-approval-status">
                            Pending
                        </span>
                    </div>

                    <div class="professional-sidebar-meta p-3 mb-4 rounded-3" style="background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fa-solid fa-location-dot text-primary"></i>
                            </div>
                            <div>
                                <p class="small text-uppercase text-muted mb-0">Location</p>
                                <p class="fw-semibold mb-0" id="professional-sidebar-location">Not set</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fa-solid fa-chart-line text-warning"></i>
                            </div>
                            <div>
                                <p class="small text-uppercase text-muted mb-0">Rate</p>
                                <div id="professional-sidebar-rate" class="d-flex align-items-center">
                                    <span class="text-muted small">No rating</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="professional-sidebar-nav d-grid gap-2">
                        <button
                            type="button"
                            class="btn btn-dark text-start professional-nav-button"
                            data-view="browse-jobs"
                        >
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Browse Jobs
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark text-start professional-nav-button"
                            data-view="my-contracts"
                        >
                            <i class="fa-solid fa-file-signature text-success me-2"></i> My Contracts
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark text-start professional-nav-button"
                            data-view="my-applications"
                        >
                            <i class="fa-solid fa-paper-plane text-primary me-2"></i> My Applications
                        </button>
                    </div>

                    <div class="mt-auto pt-3 border-top">
                        <button
                            type="button"
                            id="professional-settings-button"
                            class="btn btn-outline-secondary w-100 text-start"
                        >
                            <i class="fa-solid fa-gear me-2"></i> Settings
                        </button>
                    </div>
                </aside>

                <div>
                    @include('professional.components.stats')
                    @include('professional.components.jobs-list')
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade professional-settings-modal" id="professional-settings-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center rounded-3 border p-3 mb-3">
                        <div>
                            <h6 class="mb-1">Dark Mode</h6>
                            <p class="text-muted small mb-0">Switch the professional dashboard theme.</p>
                        </div>
                        <button type="button" id="professional-dark-mode-toggle" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-moon me-1"></i>
                            <span id="professional-dark-mode-label">Enable</span>
                        </button>
                    </div>

                    <div class="rounded-3 border border-danger-subtle p-3">
                        <h6 class="text-danger mb-1">Delete Account</h6>
                        <p class="text-muted small mb-3">This action will permanently remove your account.</p>
                        <button type="button" id="professional-delete-account-button" class="btn btn-outline-danger btn-sm">
                            <i class="fa-solid fa-trash me-1"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade professional-settings-modal" id="professional-apply-invalid-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Cannot Apply</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="professional-apply-invalid-message">
                        You cannot apply for this job right now.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Letter Modal -->
    <div class="modal fade" id="apply-cover-letter-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-paper-plane me-2"></i>Apply for Job</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="apply-cover-letter-form">
                    <div class="modal-body">
                        <p class="text-muted mb-3">Write a brief cover letter to let the client know why you're the right fit for this job.</p>
                        <div class="mb-3">
                            <label for="cover-letter-input" class="form-label fw-semibold">Cover Letter</label>
                            <textarea class="form-control" id="cover-letter-input" rows="5" 
                                placeholder="Describe your experience and why you're perfect for this job..." 
                                maxlength="1000"></textarea>
                            <div class="form-text text-end"><span id="cover-letter-count">0</span>/1000</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-cover-letter-btn">
                            <i class="fa-solid fa-paper-plane me-1"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof window.requireRole === "function") {
                window.requireRole("professional");
            }
        });
    </script>
</body>
</html>
