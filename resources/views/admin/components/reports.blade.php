<section class="admin-content-section" id="reports-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #dc3545 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">All Reports</h2>
                    <p class="text-muted mb-0">View and resolve user reports.</p>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="loadReports()">
                    <i class="fa-solid fa-rotate-right me-1"></i> Reload
                </button>
            </div>
            <div id="reports-table-area">
                <div class="text-muted">Loading reports...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="report-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-flag me-2"></i>Report Details</h5>
                    <p class="mb-0 opacity-75 small" id="report-modal-subtitle">Loading...</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="report-view-modal-body"></div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="report-resolve-btn"><i class="fa-solid fa-check me-1"></i>Resolve</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="report-resolve-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-gavel me-2"></i>Resolve Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="report-resolve-detail"></div>
                <hr>
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-wrench me-2"></i>Select Action</h6>
                <div class="mb-3">
                    <select id="report-action-select" class="form-select">
                        <option value="">Select an action...</option>
                        <option value="no_action">No Action</option>
                        <option value="suspend_user">Suspend Reported User</option>
                        <option value="cancel_contract">Cancel Contract</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Notes (Optional)</label>
                    <textarea id="report-resolution-notes" class="form-control" rows="3" placeholder="Add resolution notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="report-resolve-confirm-btn"><i class="fa-solid fa-check me-1"></i>Confirm Resolution</button>
            </div>
        </div>
    </div>
</div>
