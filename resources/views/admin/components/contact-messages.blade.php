<section class="admin-content-section" id="contact-messages-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #20c997 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">Contact Messages</h2>
                    <p class="text-muted mb-0">Manage messages from Contact page.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <select id="admin-contact-filter" class="form-select form-select-sm" style="min-width: 140px;">
                        <option value="unread">Unread</option>
                        <option value="all">All</option>
                    </select>
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="loadAdminContactMessages()">
                        <i class="fa-solid fa-rotate-right me-1"></i> Reload
                    </button>
                </div>
            </div>

            <div id="admin-contact-messages-feedback" class="mb-3 d-none"></div>
            <div id="admin-contact-messages-table-area">
                <div class="text-muted">Loading contact messages...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="admin-contact-message-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contact Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <div class="text-muted small">Name</div>
                    <div class="fw-semibold" id="admin-contact-message-view-name">N/A</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Email</div>
                    <div class="fw-semibold" id="admin-contact-message-view-email">N/A</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Date</div>
                    <div class="fw-semibold" id="admin-contact-message-view-date">N/A</div>
                </div>
                <div>
                    <div class="text-muted small">Message</div>
                    <div class="border rounded p-3 bg-light" id="admin-contact-message-view-message" style="white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
