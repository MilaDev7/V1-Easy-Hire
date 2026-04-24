<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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

        .client-sidebar {
            height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 991.98px) {
            .client-sidebar {
                position: static !important;
                width: 100% !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }

            .client-dashboard-main {
                margin-left: 0 !important;
                padding: 24px 16px 64px !important;
            }
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
<body class="bg-light" style="background-color: #f8fafc;">
    <div id="client-dashboard-loader" class="dashboard-loader">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted mb-0">Loading dashboard...</p>
        </div>
    </div>

    <div id="client-dashboard-shell">
    @include('client.components.leftnav')

    <div class="client-dashboard-main" style="margin-left: 280px; min-height: 100vh; padding: 24px 24px 140px;">
        <div class="card border-0 shadow-sm mb-4" style="background: #e7f1ff; border-top: 4px solid #0d6efd !important;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <p class="text-muted small mb-0">Client Dashboard</p>
                        <h1 class="h4 mb-0">Overview</h1>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-light border position-relative" data-bs-toggle="dropdown" aria-expanded="false" id="client-notification-bell">
                                <i class="fa-solid fa-bell"></i>
                                <span id="client-notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width: 340px;">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <strong class="small">Notifications</strong>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="client-notification-mark-all">Mark all read</button>
                                </div>
                                <div id="client-notification-list" style="max-height: 320px; overflow-y: auto;">
                                    <div class="text-muted small p-3">Loading...</div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ asset('images/user1.jpg') }}" id="client-topbar-photo" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(0,0,0,0.1); display: none;">
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="/client/dashboard"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                                <li><button type="button" class="dropdown-item" id="client-topbar-dark-mode"><i class="fa-solid fa-moon me-2"></i>Dark Mode</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('components.dashboard-plan-card', [
            'idPrefix' => '',
            'planTitle' => 'Current Plan',
            'capacityLabel' => 'Plan Capacity',
            'durationLabel' => 'Duration',
            'expiryLabel' => 'Expires',
            'requestsLabel' => 'Direct Requests',
            'gradientStart' => '#3b4f8a',
            'gradientEnd' => '#2c3e73',
            'borderColor' => '#5b7cfa',
        ])

        @include('client.components.stats')

        <div id="content-area">
            @include('client.components.jobposts-list')
        </div>
    </div>

    @include('client.components.subscription')

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
                        <input type="text" class="form-control" id="direct-request-title">
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
    </div>

    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/client.js') }}?v={{ filemtime(public_path('js/client.js')) }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/direct-request.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof window.requireRole === "function") {
                window.requireRole("client");
            }
        });
    </script>
</body>
</html>
