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

<div class="modal fade" id="user-details-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="contact-user-id">
                <div class="mb-2">
                    <div class="text-muted small">Name</div>
                    <div class="fw-semibold" id="detail-user-name">N/A</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold" id="detail-user-email">N/A</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Role</div>
                    <div class="fw-semibold text-capitalize" id="detail-user-role">N/A</div>
                </div>
                <div>
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold" id="detail-user-status">N/A</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="open-contact-user-modal-btn">
                    <i class="fa-solid fa-envelope me-1"></i> Contact User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contact-user-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="contact-user-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="contact-user-subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="contact-user-subject" maxlength="160" placeholder="Optional subject">
                    </div>
                    <div class="mb-3">
                        <label for="contact-user-message" class="form-label">Message</label>
                        <textarea id="contact-user-message" class="form-control" rows="5" required placeholder="Write your message"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="contact-user-send-email">
                        <label class="form-check-label" for="contact-user-send-email">
                            Also send by email (if user email exists)
                        </label>
                    </div>
                    <div id="contact-user-feedback" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="contact-user-submit-btn">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
