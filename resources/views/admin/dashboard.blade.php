<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.08), transparent 28%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: #0f172a;
        }

        .admin-dashboard-shell {
            min-height: 100vh;
            padding: 24px 24px 140px;
            margin-left: 300px;
        }

        .admin-dashboard-container {
            max-width: none;
            margin: 0;
        }

        .admin-sidebar {
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

        .admin-sidebar-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .admin-sidebar-nav .btn {
            border-radius: 16px;
            padding: 0.9rem 1rem;
            font-weight: 600;
        }

        .admin-content-card,
        .admin-stat-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .admin-content-card,
        .admin-stat-card {
            border-radius: 20px;
        }

        .admin-sidebar-item {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .admin-sidebar-item:hover {
            background: rgba(111, 66, 193, 0.1);
            color: #6f42c1;
        }

        .admin-sidebar-item:hover .admin-collapse-icon {
            color: #6f42c1;
        }

        .admin-sidebar-item.active {
            background: #6f42c1;
            color: white;
        }

        .admin-sidebar-children {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }

        .admin-sidebar-children.show {
            max-height: 300px;
        }

        .admin-sidebar-child {
            padding: 0.6rem 1rem 0.6rem 2rem;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .admin-sidebar-child:hover {
            background: rgba(111, 66, 193, 0.15);
            color: #6f42c1;
            font-weight: 600;
            transform: translateX(4px);
        }

        .admin-sidebar-child.active {
            background: rgba(111, 66, 193, 0.1);
            color: #6f42c1;
            font-weight: 600;
        }

        .admin-collapse-icon {
            transition: transform 0.3s ease;
        }

        .admin-collapse-icon.rotated {
            transform: rotate(180deg);
        }

        @media (max-width: 991.98px) {
            .admin-dashboard-shell {
                margin-left: 0;
                padding: 24px 16px 64px;
            }

            .admin-sidebar {
                position: static;
                width: 100%;
                border-radius: 0;
            }
        }

        body.admin-dashboard-dark {
            background: #111827 !important;
            color: #f9fafb;
        }

        body.admin-dashboard-dark .bg-light,
        body.admin-dashboard-dark .card,
        body.admin-dashboard-dark .admin-sidebar,
        body.admin-dashboard-dark .admin-content-card,
        body.admin-dashboard-dark .admin-stat-card {
            background: #1f2937 !important;
            color: #f9fafb !important;
            border-color: #374151 !important;
        }

        body.admin-dashboard-dark .text-muted {
            color: #9ca3af !important;
        }

        body.admin-dashboard-dark .admin-sidebar-item,
        body.admin-dashboard-dark .admin-sidebar-child {
            color: #f9fafb;
        }

        body.admin-dashboard-dark .admin-sidebar-item:hover,
        body.admin-dashboard-dark .admin-sidebar-child:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #c4b5fd;
        }

        body.admin-dashboard-dark .admin-sidebar-item:hover .admin-collapse-icon,
        body.admin-dashboard-dark .admin-sidebar-child:hover {
            color: #c4b5fd;
        }

        body.admin-dashboard-dark .form-control,
        body.admin-dashboard-dark .form-select {
            background: #1f2937;
            color: #f9fafb;
            border-color: #374151;
        }

        body.admin-dashboard-dark .table {
            color: #f9fafb;
        }

        body.admin-dashboard-dark .btn-close {
            filter: invert(1);
        }

        .table tbody tr:hover {
            background-color: rgba(111, 66, 193, 0.05);
        }

        body.admin-dashboard-dark .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
        }
    </style>
</head>
<body class="bg-light">
    <aside class="admin-sidebar shadow-sm p-4 d-flex flex-column">
        <div class="text-center mb-4">
            <h1 class="h4 mb-1">Admin</h1>
            <p class="text-muted small">Administrator</p>
        </div>

        <div class="admin-sidebar-nav d-grid gap-2">
            <div class="admin-sidebar-item" data-toggle="users-management">
                <i class="fa-solid fa-users me-2"></i> Users Management
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="users-management">
                <div class="admin-sidebar-child" data-view="all-users">All Users</div>
                <div class="admin-sidebar-child" data-view="suspended-users">Suspended Users</div>
                <div class="admin-sidebar-child" data-view="deleted-users">Deleted Users</div>
            </div>

            <div class="admin-sidebar-item" data-toggle="professionals-menu">
                <i class="fa-solid fa-user-tie me-2"></i> Professionals
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="professionals-menu">
                <div class="admin-sidebar-child" data-view="all-professionals">All Professionals</div>
                <div class="admin-sidebar-child" data-view="pending-professionals">Pending Approvals</div>
            </div>

            <div class="admin-sidebar-item" data-toggle="jobs-contracts-menu">
                <i class="fa-solid fa-briefcase me-2"></i> Jobs & Contracts
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="jobs-contracts-menu">
                <div class="admin-sidebar-child" data-view="jobs-view">Jobs</div>
                <div class="admin-sidebar-child" data-view="contracts-view">Contracts</div>
            </div>

            <div class="admin-sidebar-item" data-toggle="reports-menu">
                <i class="fa-solid fa-flag me-2"></i> Reports
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="reports-menu">
                <div class="admin-sidebar-child" data-view="reports">Pending Reports</div>
                <div class="admin-sidebar-child" data-view="resolved-reports">Resolved Reports</div>
            </div>

            <div class="admin-sidebar-item" data-toggle="payments-menu">
                <i class="fa-solid fa-credit-card me-2"></i> Payments
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="payments-menu">
                <div class="admin-sidebar-child" data-view="all-payments">All Payments</div>
                <div class="admin-sidebar-child" data-view="pending-payments">Pending</div>
            </div>

            <div class="admin-sidebar-item" data-toggle="plans-menu">
                <i class="fa-solid fa-layer-group me-2"></i> Plans
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="plans-menu">
                <div class="admin-sidebar-child" data-view="plans">Subscription Plans</div>
            </div>
        </div>

        <div class="mt-auto pt-3 border-top">
            <div class="admin-sidebar-item" data-toggle="settings-menu" style="margin: 0;">
                <i class="fa-solid fa-gear me-2"></i> Settings
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="settings-menu">
                <div class="admin-sidebar-child" style="cursor: pointer;" onclick="event.stopPropagation(); toggleAdminDarkMode();">
                    <i class="fa-solid fa-moon me-2"></i> Dark Mode
                </div>
            </div>
        </div>
    </aside>

    <main class="admin-dashboard-shell" id="admin-dashboard">
        <div class="card border-0 shadow-sm mb-4 position-relative" style="background: #f0e6ff; border-top: 4px solid #6f42c1 !important;">
            <div class="card-body text-center py-4">
                <div>
                    <p class="text-muted small mb-0">Admin Dashboard</p>
                    <h1 class="h4 mb-0">Overview</h1>
                </div>

                <button type="button" class="btn btn-outline-danger btn-sm position-absolute end-0 me-4 top-50 translate-middle-y" onclick="logout()">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </button>
            </div>
        </div>

        <div class="admin-dashboard-container">
            @include('admin.components.stats')
            @include('admin.components.pending-professionals')
            @include('admin.components.all-professionals')
            @include('admin.components.users')
            @include('admin.components.jobs')
            @include('admin.components.contracts')
            @include('admin.components.reports')
            @include('admin.components.plans')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("Admin dashboard loading...");
            console.log("Token:", localStorage.getItem("token"));
            console.log("Role:", localStorage.getItem("role"));
            
            if (typeof window.requireRole === "function") {
                window.requireRole("admin");
            }
            
            // Load dark mode preference
            if (localStorage.getItem('admin_dark_mode') === '1') {
                document.body.classList.add('admin-dashboard-dark');
            }
            
            loadAdminStats();
            loadPendingProfessionals();
        });
    </script>
</body>
</html>
