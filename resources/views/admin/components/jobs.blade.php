<section class="admin-content-section" id="jobs-section" style="display: none;">
    <div class="card border-0 shadow-sm admin-content-card" style="border-top: 4px solid #0d6efd !important;">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <p class="text-muted text-uppercase small mb-1">Workspace</p>
                    <h2 class="h4 mb-1">All Jobs</h2>
                    <p class="text-muted mb-0">View all job posts on the platform.</p>
                </div>
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill" onclick="loadJobs()">
                    <i class="fa-solid fa-rotate-right me-1"></i> Reload
                </button>
            </div>
            <div id="jobs-table-area">
                <div class="text-muted">Loading jobs...</div>
            </div>
        </div>
    </div>
</section>
