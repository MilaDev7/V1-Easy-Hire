<section class="professional-list-section">
    <div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid #6f42c1 !important;">
        <div class="card-body p-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 text-secondary">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>
                        <input
                            id="professional-skill"
                            class="form-control form-control-lg border-0 ps-1"
                            placeholder="Skill (e.g., Electrician)"
                        >
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 text-secondary">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <input
                            id="professional-location"
                            class="form-control form-control-lg border-0 ps-1"
                            placeholder="Location (e.g., Addis)"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <button
                        type="button"
                        id="professional-search-button"
                        class="btn btn-success btn-sm w-100 rounded-pill fw-bold h-100 d-flex align-items-center justify-content-center gap-2"
                        onclick="if(typeof window.loadProfessionalsResults==='function'){window.loadProfessionalsResults(document.getElementById('professional-skill').value, document.getElementById('professional-location').value);}"
                    >
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="professionals-results">
        <div class="text-muted">Loading professionals...</div>
    </div>
</section>
