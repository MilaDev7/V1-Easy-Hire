<section class="admin-content-section" id="payments-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #fd7e14 !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1" id="payments-section-title">Payments</h2>
                    <p class="text-muted mb-0" id="payments-section-subtitle">Track completed and pending payment activity.</p>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="window.reloadAdminPaymentsView()">
                    <i class="fa-solid fa-rotate-right me-1"></i> Reload
                </button>
            </div>

            <div id="payments-table-area">
                <div class="text-muted">Loading payments...</div>
            </div>
        </div>
    </div>
</section>
