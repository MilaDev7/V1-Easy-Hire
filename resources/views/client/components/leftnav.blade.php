<aside
    class="client-sidebar d-flex flex-column align-items-center text-center bg-white shadow-sm p-4"
    style="position: fixed; top: 0; left: 0; width: 280px; min-height: 100vh; border-top: 4px solid #0d6efd !important;"
>
    <div class="w-100 text-start mb-4">
        <a href="/" class="navbar-brand fw-bold text-success text-decoration-none fs-3">
            EasyHire
        </a>
    </div>

    <div class="mb-3">
        <img
            src="{{ asset('images/user1.jpg') }}"
            alt="Client Profile Photo"
            id="client-sidebar-photo"
            class="rounded-circle object-fit-cover border"
            style="width: 96px; height: 96px;"
        >
    </div>

    <h2 class="h5 fw-bold mb-4" id="client-name">Client Name</h2>

    <div class="d-grid gap-2 w-100">
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="post-job"
        >
            <i class="fa-solid fa-plus-circle text-success me-2"></i> Post Job
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="job-posts"
        >
            <i class="fa-solid fa-list-ul text-primary me-2"></i> Job Posts
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="professionals"
        >
            <i class="fa-solid fa-users text-info me-2"></i> View Professionals
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="applications"
        >
            <i class="fa-solid fa-file-lines text-warning me-2"></i> Applications
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="contracts"
        >
            <i class="fa-solid fa-file-contract text-secondary me-2"></i> All Contracts
        </button>
    </div>

    <div class="mt-auto pt-4 w-100">
        <div class="d-grid gap-2">
            <button
                type="button"
                id="settings-button"
                class="btn btn-outline-secondary text-start"
            >
                <i class="fa-solid fa-gear me-2"></i> Settings
            </button>
        </div>
    </div>
</aside>

<div class="modal fade" id="dashboard-settings-modal" tabindex="-1" aria-hidden="true">
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
                        <p class="text-muted small mb-0">Switch the dashboard theme inside Settings.</p>
                    </div>
                    <button type="button" id="dark-mode-toggle" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-moon me-1"></i>
                        <span id="dark-mode-label">Enable</span>
                    </button>
                </div>

                <div class="rounded-3 border border-danger-subtle p-3">
                    <h6 class="text-danger mb-1">Delete Account</h6>
                    <p class="text-muted small mb-3">This action will permanently remove your account.</p>
                    <button type="button" id="delete-account-button" class="btn btn-outline-danger btn-sm">
                        <i class="fa-solid fa-trash me-1"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-account-modal" tabindex="-1" aria-hidden="true">
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-delete-account-button" class="btn btn-danger">Yes</button>
            </div>
        </div>
    </div>
</div>
