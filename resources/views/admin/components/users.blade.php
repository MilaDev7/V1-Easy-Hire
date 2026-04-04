<section class="admin-content-section" id="users-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #6f42c1 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1" id="users-section-title">Users</h2>
                    <p class="text-muted mb-0">Manage platform users.</p>
                </div>
                <button
                    type="button"
                    class="btn btn-outline-dark btn-sm rounded-pill"
                    onclick="loadUsers()"
                >
                    <i class="fa-solid fa-rotate-right me-1"></i> Reload
                </button>
            </div>

            <div id="users-feedback" class="mb-3 d-none"></div>
            <div id="users-table-area">
                <div class="text-muted">Loading users...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="users-action-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="users-action-modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="users-action-modal-body">
                Are you sure?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="users-action-confirm-btn">Confirm</button>
            </div>
        </div>
    </div>
</div>
