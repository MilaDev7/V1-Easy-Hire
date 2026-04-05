<section class="admin-content-section" id="contracts-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #198754 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">All Contracts</h2>
                    <p class="text-muted mb-0">View and manage all contracts.</p>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="loadContracts()">
                    <i class="fa-solid fa-rotate-right me-1"></i> Reload
                </button>
            </div>
            <div id="contracts-table-area">
                <div class="text-muted">Loading contracts...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="contract-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-contract me-2"></i>Contract Details</h5>
                    <p class="mb-0 opacity-75 small" id="contract-modal-subtitle">Loading...</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="contract-view-modal-body"></div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="contract-action-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contract-action-modal-title">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contract-action-modal-body">Are you sure?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="contract-action-confirm-btn">Confirm</button>
            </div>
        </div>
    </div>
</div>
