<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Dashboard</title>
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
            height: 100vh;
            max-height: 100vh;
            border-radius: 0 28px 28px 0;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border-top: 4px solid #6f42c1;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .professional-sidebar-photo {
            width: 128px;
            height: 128px;
            object-fit: cover;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .professional-sidebar-meta {
            border-radius: 18px;
            background: #f8fafc;
        }

        .professional-meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.75rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .professional-meta-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .professional-meta-label {
            font-size: 0.68rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.1rem;
        }

        .professional-meta-value {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .professional-rate-display {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .professional-rate-score {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            padding: 0.12rem 0.5rem;
            border-radius: 999px;
            background: #fff3cd;
            color: #664d03;
            font-size: 0.76rem;
            font-weight: 700;
            border: 1px solid rgba(102, 77, 3, 0.2);
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
        body.professional-dashboard-dark .professional-meta-item,
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

        body.professional-dashboard-dark .professional-meta-label {
            color: #94a3b8;
        }

        body.professional-dashboard-dark .professional-meta-value {
            color: #e5eefb;
        }

        body.professional-dashboard-dark .professional-rate-score {
            background: #1e293b;
            color: #facc15;
            border-color: rgba(250, 204, 21, 0.35);
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

        .dashboard-loader {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(2px);
        }
    </style>
</head>
<body class="bg-light" style="background-color: #eef2f7;">
    <div id="professional-dashboard-loader" class="dashboard-loader">
        <div class="text-center">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <p class="text-muted mb-0">Loading dashboard...</p>
        </div>
    </div>

    <main class="professional-dashboard-shell" id="professional-dashboard">
        <div class="card border-0 shadow-sm mb-4" style="background: #e6fffa; border-top: 4px solid #20c997 !important;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <p class="text-muted small mb-0">Professional Dashboard</p>
                        <h1 class="h4 mb-0">Overview</h1>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('images/user1.jpg') }}" id="professional-topbar-photo" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(0,0,0,0.1);">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/pro/dashboard"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                            <li><button type="button" class="dropdown-item" id="professional-topbar-dark-mode"><i class="fa-solid fa-moon me-2"></i>Dark Mode</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="professional-dashboard-container">
            <div class="professional-dashboard-grid">
                <aside class="professional-sidebar shadow-sm p-4 d-flex flex-column">
                    <div class="text-center mb-4">
                        <a href="/" class="navbar-brand d-flex align-items-center justify-content-center gap-2 text-decoration-none mb-3 mx-auto" style="width: fit-content;">
                            <div style="background: linear-gradient(135deg, #198754, #20c997); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);">
                                <i class="fa-solid fa-bolt text-white fs-5"></i>
                            </div>
                            <span class="fw-bold" style="background: linear-gradient(135deg, #198754, #20c997); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">EasyHire</span>
                        </a>
                        <img
                            src="{{ asset('images/user1.jpg') }}"
                            alt="Professional Photo"
                            id="professional-dashboard-photo"
                            class="professional-sidebar-photo rounded border mb-3"
                        >
                        <h1 class="h4 mb-1" id="professional-dashboard-name">Loading...</h1>
                        <p class="text-muted mb-2" id="professional-sidebar-skill">Loading...</p>
                        <span class="badge rounded-pill text-bg-warning" id="professional-approval-status">
                            Loading...
                        </span>
                    </div>

                    <div class="professional-sidebar-meta p-3 mb-4 rounded-3" style="background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%);">
                        <div class="professional-meta-item mb-2">
                            <div class="professional-meta-icon" style="background: rgba(13, 110, 253, 0.14);">
                                <i class="fa-solid fa-location-dot text-primary"></i>
                            </div>
                            <div>
                                <p class="professional-meta-label">Location</p>
                                <p class="professional-meta-value" id="professional-sidebar-location">Loading...</p>
                            </div>
                        </div>
                        <div class="professional-meta-item">
                            <div class="professional-meta-icon" style="background: rgba(255, 193, 7, 0.2);">
                                <i class="fa-solid fa-chart-line text-warning"></i>
                            </div>
                            <div>
                                <p class="professional-meta-label">Rate</p>
                                <div id="professional-sidebar-rate" class="professional-rate-display">
                                    <span class="text-muted small">Loading...</span>
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
                        <button
                            type="button"
                            class="btn btn-outline-dark text-start professional-nav-button"
                            data-view="direct-requests"
                        >
                            <i class="fa-solid fa-envelope text-warning me-2"></i> Requests
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark text-start professional-nav-button"
                            data-view="reviews-reports"
                        >
                            <i class="fa-solid fa-star text-info me-2"></i> Reviews & Reports
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark text-start professional-nav-button"
                            data-view="portfolio"
                        >
                            <i class="fa-solid fa-images text-secondary me-2"></i> Portfolio
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Profile Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-user me-2"></i>Profile</h6>
                        
                        <!-- Photo -->
                        <div class="text-center mb-4 position-relative d-inline-block" style="width: 100px; height: 100px; margin: 0 auto; display: block;">
                            <img id="professional-settings-profile-preview" src="{{ asset('images/user1.jpg') }}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            <input type="file" id="professional-settings-photo-input" class="d-none" accept="image/*">
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute bottom-0 end-0 rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="document.getElementById('professional-settings-photo-input').click()">
                                <i class="fa-solid fa-pen fa-xs"></i>
                            </button>
                        </div>
                        
                        <!-- Name -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label text-muted small mb-0">Name</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="professional-profile-name">
                                    <i class="fa-solid fa-pen fa-xs"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control bg-light" id="professional-profile-name" name="name" readonly>
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label text-muted small mb-0">Email</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="professional-profile-email">
                                    <i class="fa-solid fa-pen fa-xs"></i>
                                </button>
                            </div>
                            <input type="email" class="form-control bg-light" id="professional-profile-email" name="email" readonly>
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label text-muted small mb-0">Phone</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="professional-profile-phone">
                                    <i class="fa-solid fa-pen fa-xs"></i>
                                </button>
                            </div>
                            <input type="tel" class="form-control bg-light" id="professional-profile-phone" name="phone" maxlength="10" readonly>
                        </div>
                        
                        <!-- Location -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label text-muted small mb-0">Location</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="professional-profile-location">
                                    <i class="fa-solid fa-pen fa-xs"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control bg-light" id="professional-profile-location" name="location" readonly>
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-primary" id="professional-save-profile-btn">
                                <i class="fa-solid fa-save me-1"></i> Save Changes
                            </button>
                        </div>
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

    <div class="modal fade" id="professional-delete-account-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="professional-confirm-delete-account-button" class="btn btn-danger">Yes</button>
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

    <!-- Direct Request Action Modal -->
    <div class="modal fade" id="direct-request-action-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0" id="action-modal-header" style="background: #6c757d; color: white;">
                    <h5 class="modal-title fw-bold" id="action-modal-title">
                        <i class="fa-solid fa-handshake me-2"></i>Direct Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div id="action-modal-icon" class="mb-3">
                        <i class="fa-solid fa-envelope-open-text fa-4x" style="color: #6c757d;"></i>
                    </div>
                    <h5 class="mb-2" id="action-modal-request-title"></h5>
                    <p class="text-muted mb-2" id="action-modal-client-name"></p>
                    <div id="action-modal-budget" class="badge fs-6 mb-3" style="background: #198754;"></div>
                    <p class="mb-0" id="action-modal-message"></p>
                </div>
                <div class="modal-footer border-top justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn px-4" id="confirm-action-btn" style="background: #6c757d; color: white;">
                        <i class="fa-solid fa-check me-1"></i>Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/professional.js') }}"></script>
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
