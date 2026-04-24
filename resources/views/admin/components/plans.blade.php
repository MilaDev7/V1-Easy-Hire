<section class="admin-content-section" id="plans-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #6f42c1 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">Subscription Plans</h2>
                    <p class="text-muted mb-0">Manage subscription plans for clients and professionals.</p>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                    <div>
                        <select id="admin-plan-view" class="form-select form-select-sm">
                            <option value="client">Client Plans</option>
                            <option value="professional">Professional Plans</option>
                            <option value="all">All Plans</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill" onclick="showCreatePlanModal()">
                        <i class="fa-solid fa-plus me-1"></i> Create Plan
                    </button>
                </div>
            </div>
            <div id="plans-table-area">
                <div class="text-muted">Loading plans...</div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="plan-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="plan-modal-title"><i class="fa-solid fa-layer-group me-2"></i>Create Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="plan-id">
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa-solid fa-tag me-2 text-primary"></i>Plan Name</label>
                    <input type="text" class="form-control" id="plan-name" placeholder="e.g., Basic, Premium">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fa-solid fa-layer-group me-2 text-secondary"></i>Plan Type</label>
                    <select class="form-select" id="plan-scope">
                        <option value="client">Client Plan</option>
                        <option value="professional_monthly">Professional Monthly Plan</option>
                        <option value="professional_extra">Professional Extra Pack</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-dollar-sign me-2 text-success"></i>Price (Br)</label>
                        <input type="number" class="form-control" id="plan-price" placeholder="0.00" step="0.01" min="0">
                    </div>
                    <div class="col-md-6 mb-3" id="plan-job-limit-wrap">
                        <label class="form-label fw-bold"><i class="fa-solid fa-briefcase me-2 text-warning"></i>Job Posts Limit</label>
                        <input type="number" class="form-control" id="plan-job-limit" placeholder="e.g., 5" min="1">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3" id="plan-duration-wrap">
                        <label class="form-label fw-bold"><i class="fa-solid fa-clock me-2 text-info"></i>Duration (Days)</label>
                        <input type="number" class="form-control" id="plan-duration" placeholder="e.g., 30" min="1">
                    </div>
                    <div class="col-md-6 mb-3" id="plan-direct-requests-wrap">
                        <label class="form-label fw-bold"><i class="fa-solid fa-paper-plane me-2 text-purple"></i>Direct Requests Limit</label>
                        <input type="number" class="form-control" id="plan-direct-requests" placeholder="e.g., 3" min="0">
                        <small class="text-muted">0 = Not available</small>
                    </div>
                </div>
                <div class="row d-none" id="plan-apply-monthly-wrap">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-calendar-check me-2 text-success"></i>Apply Limit / Month</label>
                        <input type="number" class="form-control" id="plan-apply-monthly" placeholder="e.g., 20" min="0">
                    </div>
                </div>
                <div class="row d-none" id="plan-extra-apply-wrap">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Extra Applies Quantity</label>
                        <input type="number" class="form-control" id="plan-extra-apply" placeholder="e.g., 10" min="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePlan()"><i class="fa-solid fa-check me-1"></i>Save Plan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="plan-delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-trash me-2"></i>Delete Plan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="fa-solid fa-exclamation-triangle text-danger fa-4x"></i>
                </div>
                <h5>Are you sure?</h5>
                <p class="text-muted">This action cannot be undone. The plan will be permanently deleted.</p>
                <input type="hidden" id="delete-plan-id">
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeletePlan()"><i class="fa-solid fa-trash me-1"></i>Delete</button>
            </div>
        </div>
    </div>
</div>
