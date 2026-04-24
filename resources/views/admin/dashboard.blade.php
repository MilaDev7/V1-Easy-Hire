<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
<body class="bg-light">
    <div id="admin-dashboard-loader" class="dashboard-loader">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted mb-0">Loading dashboard...</p>
        </div>
    </div>

    <aside class="admin-sidebar shadow-sm p-4 d-flex flex-column">
        <div class="text-center mb-4">
            <a href="/" class="navbar-brand d-flex align-items-center justify-content-center gap-2 text-decoration-none mb-3 mx-auto" style="width: fit-content;">
                <div style="background: linear-gradient(135deg, #198754, #20c997); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);">
                    <i class="fa-solid fa-handshake text-white fs-5"></i>
                </div>
                <span class="fw-bold" style="background: linear-gradient(135deg, #198754, #20c997); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">EasyHire</span>
            </a>
            <h1 class="h4 mb-1">Admin</h1>
            <p class="text-muted small">Administrator</p>
        </div>

        <div class="admin-sidebar-nav d-grid gap-2">
            <div class="admin-sidebar-item" data-toggle="users-management">
                <i class="fa-solid fa-users me-2"></i> Users Management
                <i class="fa-solid fa-chevron-down float-end admin-collapse-icon"></i>
            </div>
            <div class="admin-sidebar-children" id="users-management">
                <div class="admin-sidebar-child" data-view="all-users" data-testid="admin-nav-all-users">All Users</div>
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

    </aside>

    <main class="admin-dashboard-shell" id="admin-dashboard">
        <div class="card border-0 shadow-sm mb-4" style="background: #f0e6ff; border-top: 4px solid #6f42c1 !important;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <p class="text-muted small mb-0">Admin Dashboard</p>
                        <h1 class="h4 mb-0">Overview</h1>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('images/user1.jpg') }}" id="admin-topbar-photo" alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid rgba(0,0,0,0.1);">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/admin/dashboard"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                            <li><button type="button" class="dropdown-item" onclick="toggleAdminDarkMode()"><i class="fa-solid fa-moon me-2"></i>Dark Mode</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
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
            @include('admin.components.payments')
            @include('admin.components.plans')
        </div>
    </main>

    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}?v={{ @filemtime(public_path('js/admin.js')) }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

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
            
        });
    </script>
</body>
</html>
