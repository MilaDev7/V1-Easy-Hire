<aside
    class="client-sidebar d-flex flex-column align-items-center text-center bg-white shadow-sm p-4"
    style="position: fixed; top: 0; left: 0; width: 280px; min-height: 100vh; border-top: 4px solid #0d6efd !important;"
>
    <div class="w-100 text-start mb-4">
        <a href="/" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none fs-5">
            <div style="background: linear-gradient(135deg, #198754, #20c997); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);">
                <i class="fa-solid fa-bolt text-white fs-5"></i>
            </div>
            <span class="fw-bold" style="background: linear-gradient(135deg, #198754, #20c997); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">EasyHire</span>
        </a>
    </div>

    <div class="mb-3">
        <img
            src="{{ asset('images/user1.jpg') }}"
            alt="Client Profile Photo"
            id="client-sidebar-photo"
            class="rounded border object-fit-cover"
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
                        <img id="settings-profile-preview" src="{{ asset('images/user1.jpg') }}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        <input type="file" id="settings-photo-input" class="d-none" accept="image/*">
                        <button type="button" class="btn btn-sm btn-outline-secondary position-absolute bottom-0 end-0 rounded-circle" style="width: 28px; height: 28px; padding: 0;" onclick="document.getElementById('settings-photo-input').click()">
                            <i class="fa-solid fa-pen fa-xs"></i>
                        </button>
                    </div>
                    
                    <!-- Name -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label text-muted small mb-0">Name</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="profile-name">
                                <i class="fa-solid fa-pen fa-xs"></i>
                            </button>
                        </div>
                        <input type="text" class="form-control bg-light" id="profile-name" name="name" readonly>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label text-muted small mb-0">Email</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary edit-field-btn py-0" data-field="profile-email">
                                <i class="fa-solid fa-pen fa-xs"></i>
                            </button>
                        </div>
                        <input type="email" class="form-control bg-light" id="profile-email" name="email" readonly>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" class="btn btn-primary" id="save-profile-btn">
                            <i class="fa-solid fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>

                <hr class="my-4">

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
