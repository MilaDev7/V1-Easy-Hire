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
        <div class="card border-0 shadow-sm mb-4 position-relative" style="background: #e7f1ff; border-top: 4px solid #0d6efd !important;">
            <div class="card-body text-center py-4">
                <div>
                    <p class="text-muted small mb-0">Client Dashboard</p>
                    <h1 class="h4 mb-0">Overview</h1>
                </div>

                <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 me-4 top-50 translate-middle-y" onclick="logout()">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </button>
            </div>
        </div>

        @include('client.components.stats')

        <div id="content-area">
            @include('client.components.jobposts-list')
        </div>
    </div>

    @include('client.components.subscription')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof window.requireRole === "function") {
                window.requireRole("client");
            }
            
            setTimeout(function() {
                if (typeof window.bindProfessionalSearch === "function") {
                    window.bindProfessionalSearch();
                }
            }, 100);
        });
    </script>
</body>
</html>
