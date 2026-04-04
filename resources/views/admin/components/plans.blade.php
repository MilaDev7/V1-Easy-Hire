<section class="admin-content-section" id="plans-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #198754 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">Subscription Plans</h2>
                    <p class="text-muted mb-0">Manage subscription plans.</p>
                </div>
                <button type="button" class="btn btn-dark btn-sm rounded-pill" onclick="showCreatePlanModal()">
                    <i class="fa-solid fa-plus me-1"></i> Create Plan
                </button>
            </div>
            <div id="plans-table-area">
                <div class="text-muted">Loading plans...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="admin-plan-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="admin-plan-modal-title">Create Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="plan-id">
                <div class="mb-3">
                    <label class="form-label">Plan Name</label>
                    <input type="text" class="form-control" id="plan-name" placeholder="e.g., Basic">
                </div>
                <div class="mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" class="form-control" id="plan-price" placeholder="0.00" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Duration (days)</label>
                    <input type="number" class="form-control" id="plan-duration" placeholder="30">
                </div>
                <div class="mb-3">
                    <label class="form-label">Job Posts Allowed</label>
                    <input type="number" class="form-control" id="plan-job-posts" placeholder="5">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="plan-description" rows="2" placeholder="Plan description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="plan-save-btn" onclick="savePlan()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="admin-plan-delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this plan? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="plan-delete-confirm-btn">Delete</button>
            </div>
        </div>
    </div>
</div>
