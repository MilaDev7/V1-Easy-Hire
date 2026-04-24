document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Professional dashboard module.
    const { fetchJson, postJson, deleteJson, buildHeaders } = window.EasyHireApi;
    const { toArray, setText, formatPrice, shortText, formatDate } = window.EasyHireUtils;

    // Professional UI rendering and actions.
function setProfessionalStatus(status) {
    const statusElement = document.getElementById("professional-approval-status");

    if (!statusElement) {
        return;
    }

    const safeStatus = (status || "pending").toString().toLowerCase();
    const label = safeStatus.charAt(0).toUpperCase() + safeStatus.slice(1);
    statusElement.textContent = label;
    statusElement.className = "badge rounded-pill";

    if (safeStatus === "approved") {
        statusElement.classList.add("text-bg-success");
        return;
    }

    if (safeStatus === "rejected") {
        statusElement.classList.add("text-bg-danger");
        return;
    }

    statusElement.classList.add("text-bg-warning");
}

function setProfessionalContentHeader(title, subtitle, showReloadButton) {
    setText("professional-content-title", title);
    setText("professional-content-subtitle", subtitle);

    const reloadButton = document.getElementById("professional-content-reload-button");

    if (reloadButton) {
        reloadButton.classList.toggle("d-none", !showReloadButton);
    }
}

function showProfessionalFeedback(type, message) {
    const feedback = document.getElementById("professional-content-feedback");

    if (!feedback) {
        return;
    }

    feedback.classList.remove("d-none");
    feedback.innerHTML = `<div class="alert alert-${type} mb-0">${message}</div>`;
}

function clearProfessionalFeedback() {
    const feedback = document.getElementById("professional-content-feedback");

    if (!feedback) {
        return;
    }

    feedback.classList.add("d-none");
    feedback.innerHTML = "";
}

function setActiveProfessionalNav(view) {
    document.querySelectorAll(".professional-nav-button").forEach((button) => {
        const isActive = button.dataset.view === view;

        button.classList.toggle("btn-dark", isActive);
        button.classList.toggle("text-white", isActive);
        button.classList.toggle("btn-outline-dark", !isActive);
    });
}

function getProfessionalContentArea() {
    return document.getElementById("professional-content-area");
}

let professionalJobsMode = "recommended";
let professionalJobsCache = [];
let professionalApplyPlanLoading = false;

function setProfessionalJobsTabState() {
    const recommendedTab = document.getElementById("pro-jobs-tab-recommended");
    const allTab = document.getElementById("pro-jobs-tab-all");

    if (recommendedTab) {
        const active = professionalJobsMode === "recommended";
        recommendedTab.classList.toggle("btn-dark", active);
        recommendedTab.classList.toggle("text-white", active);
        recommendedTab.classList.toggle("btn-outline-dark", !active);
    }

    if (allTab) {
        const active = professionalJobsMode === "all";
        allTab.classList.toggle("btn-dark", active);
        allTab.classList.toggle("text-white", active);
        allTab.classList.toggle("btn-outline-dark", !active);
    }
}

function bindProfessionalJobsTabs() {
    const recommendedTab = document.getElementById("pro-jobs-tab-recommended");
    const allTab = document.getElementById("pro-jobs-tab-all");

    if (recommendedTab) {
        recommendedTab.addEventListener("click", function () {
            professionalJobsMode = "recommended";
            setProfessionalJobsTabState();
            renderProfessionalJobs(professionalJobsCache);
        });
    }

    if (allTab) {
        allTab.addEventListener("click", function () {
            professionalJobsMode = "all";
            setProfessionalJobsTabState();
            renderProfessionalJobs(professionalJobsCache);
        });
    }
}

function renderProfessionalJobsSection() {
    const contentArea = getProfessionalContentArea();

    if (!contentArea) {
        return;
    }

    setProfessionalContentHeader(
        "Browse Jobs",
        "Search open jobs and apply directly from your dashboard.",
        true
    );

    contentArea.innerHTML = `
        <section class="professional-jobs-section">
            <div class="mb-4 p-3 p-lg-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, rgba(32, 201, 151, 0.14), rgba(13, 110, 253, 0.09)); border: 1px solid rgba(15, 23, 42, 0.08);">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h3 class="h5 mb-0 fw-bold"><i class="fa-solid fa-compass me-2 text-success"></i>Find Your Next Job</h3>
                    <span class="badge text-bg-light border">Live opportunities</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="pro-job-skill-search" class="form-label small text-uppercase text-muted mb-2">Skill</label>
                        <input
                            type="text"
                            id="pro-job-skill-search"
                            class="form-control"
                            placeholder="Painting, Electrician, Plumbing..."
                        >
                    </div>
                    <div class="col-md-5">
                        <label for="pro-job-location-search" class="form-label small text-uppercase text-muted mb-2">Location</label>
                        <input
                            type="text"
                            id="pro-job-location-search"
                            class="form-control"
                            placeholder="Bahir Dar, Addis Ababa..."
                        >
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button
                            type="button"
                            id="pro-job-search-button"
                            class="btn btn-success w-100 fw-semibold"
                        >
                            <i class="fa-solid fa-magnifying-glass me-1"></i>Search
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button type="button" id="pro-jobs-tab-recommended" class="btn btn-sm btn-dark">Recommended Jobs</button>
                <button type="button" id="pro-jobs-tab-all" class="btn btn-sm btn-outline-dark">All Jobs</button>
            </div>

            <div id="professional-jobs-results">
                <div class="text-muted">Loading jobs...</div>
            </div>
        </section>
    `;

    professionalJobsMode = "recommended";
    professionalJobsCache = [];
    setProfessionalJobsTabState();
    bindProfessionalJobsTabs();
    bindProfessionalSearch();
}

function renderProfessionalJobs(jobs) {
    const results = document.getElementById("professional-jobs-results");

    if (!results) {
        return;
    }

    professionalJobsCache = Array.isArray(jobs) ? jobs : [];

    const filteredJobs = professionalJobsMode === "recommended"
        ? professionalJobsCache.filter((job) => Boolean(job.skill_match))
        : professionalJobsCache;

    if (!filteredJobs.length) {
        results.innerHTML =
            professionalJobsMode === "recommended"
                ? '<div class="alert alert-light border mb-0">No recommended jobs matched your skill and filters.</div>'
                : '<div class="alert alert-light border mb-0">No open jobs matched your filters.</div>';
        return;
    }

    const totalJobs = filteredJobs.length;
    const allJobsCount = professionalJobsCache.length;
    const matchedJobs = professionalJobsCache.filter((job) => Boolean(job.skill_match)).length;
    const appliedJobs = filteredJobs.filter((job) => Boolean(job.has_applied)).length;

    const cards = filteredJobs
        .map((job) => {
            const skills = job.skills || job.skill || "N/A";
            const status = job.status || "open";
            const normalizedStatus = String(status).toLowerCase();
            const location = job.location || "N/A";
            const alreadyApplied = Boolean(job.has_applied);
            const skillMatch = Boolean(job.skill_match);
            const startDate = job.start_date ? formatDate(job.start_date) : null;
            const deadline = job.deadline ? formatDate(job.deadline) : null;
            const deadlinePast = Boolean(job.deadline) && new Date(job.deadline) < new Date(new Date().setHours(0, 0, 0, 0));
            const isOpen = normalizedStatus === "open" && !deadlinePast;
            const isExpired = normalizedStatus === "expired" || deadlinePast;
            const canApply = !alreadyApplied && skillMatch && isOpen;
            const buttonClass = canApply ? "btn btn-success" : "btn btn-secondary";
            const buttonText = alreadyApplied ? "Applied" : (isExpired ? "Expired" : "Apply");
            const skillTags = String(skills)
                .split(/[,/|]+/)
                .map((value) => value.trim())
                .filter(Boolean)
                .slice(0, 4);
            const statusBadge = isExpired
                ? '<span class="badge text-bg-danger-subtle border text-danger">Expired</span>'
                : normalizedStatus === "open"
                ? '<span class="badge text-bg-success-subtle border text-success">Open</span>'
                : `<span class="badge text-bg-light border">${status}</span>`;

            return `
                <article class="card professional-job-card shadow-sm h-100 overflow-hidden" style="border: 1px solid rgba(15, 23, 42, 0.08); border-left: 4px solid ${skillMatch ? "#20c997" : "#f59e0b"} !important;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="h5 mb-2">${job.title || "Untitled Job"}</h3>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    ${statusBadge}
                                    <span class="badge ${skillMatch ? "text-bg-success" : "text-bg-warning text-dark"}">${skillMatch ? "Skill Match" : "Needs Skill Check"}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <p class="text-muted small text-uppercase mb-1">Budget</p>
                                <p class="h5 mb-0 fw-bold text-success">${formatPrice(job.budget)}</p>
                            </div>
                        </div>

                        <p class="professional-job-description mb-3">${shortText(job.description)}</p>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-4">
                                <div class="small rounded-3 px-2 py-2 border bg-light-subtle">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Location</span>
                                    <span class="fw-semibold">${location}</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small rounded-3 px-2 py-2 border bg-light-subtle">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Start</span>
                                    <span class="fw-semibold">${startDate || "Flexible"}</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small rounded-3 px-2 py-2 border bg-light-subtle">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem;">Deadline</span>
                                    <span class="fw-semibold">${deadline || "N/A"}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-4">
                            ${(skillTags.length ? skillTags : ["General"]).map((tag) => `
                                <span class="badge rounded-pill text-bg-light border">${tag}</span>
                            `).join("")}
                        </div>

                        <div class="professional-job-meta text-muted mb-4 small">
                            ${skillMatch
                                ? '<i class="fa-solid fa-circle-check text-success me-1"></i>This job aligns with your current skill profile.'
                                : '<i class="fa-solid fa-circle-info text-warning me-1"></i>You can review requirements before applying.'}
                        </div>

                        <div class="mt-auto d-flex justify-content-end">
                            <button
                                type="button"
                                class="${buttonClass} professional-apply-button fw-semibold px-4"
                                data-job-id="${job.id}"
                                data-has-applied="${alreadyApplied ? "true" : "false"}"
                                data-skill-match="${skillMatch ? "true" : "false"}"
                                data-testid="professional-apply-${job.id}"
                                ${canApply ? "" : "disabled"}
                            >
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </article>
            `;
        })
        .join("");

    results.innerHTML = `
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge text-bg-light border">${professionalJobsMode === "recommended" ? "Recommended" : "Visible"}: ${totalJobs}</span>
            <span class="badge text-bg-light border">All Jobs: ${allJobsCount}</span>
            <span class="badge text-bg-success-subtle border text-success">Skill Match: ${matchedJobs}</span>
            <span class="badge text-bg-secondary">Applied: ${appliedJobs}</span>
        </div>
        <div class="row g-3">${cards}</div>
    `;
    bindProfessionalApplyButtons();
}

function showProfessionalApplyInvalidModal(message) {
    const pendingApprovalMessage = "You are not approved yet. The admin is reviewing your info.";
    const normalizedMessage = (message || "").toLowerCase();
    const isPendingApproval =
        normalizedMessage.includes("not approved yet") ||
        normalizedMessage.includes("admin is reviewing");

    const coverLetterModalElement = document.getElementById("apply-cover-letter-modal");
    if (coverLetterModalElement) {
        const coverModalInstance = bootstrap.Modal.getInstance(coverLetterModalElement);
        if (coverModalInstance) {
            coverModalInstance.hide();
        }
    }

    if (isPendingApproval) {
        window.alert(pendingApprovalMessage);
        return;
    }

    const modalElement = document.getElementById("professional-apply-invalid-modal");
    const messageElement = document.getElementById("professional-apply-invalid-message");

    if (!modalElement || !messageElement) {
        window.alert(message);
        return;
    }

    messageElement.textContent = message;
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function renderProfessionalJobsError() {
    const results = document.getElementById("professional-jobs-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load jobs right now.</div>';
    }
}

function bindProfessionalApplyButtons() {
    document.querySelectorAll(".professional-apply-button").forEach((button) => {
        button.addEventListener("click", function () {
            const jobId = button.dataset.jobId;
            const alreadyApplied = button.dataset.hasApplied === "true";
            const skillMatch = button.dataset.skillMatch === "true";

            if (!jobId) {
                return;
            }

            if (alreadyApplied) {
                showProfessionalApplyInvalidModal("You already applied for this job.");
                button.disabled = true;
                button.className = "btn btn-secondary professional-apply-button";
                button.textContent = "Applied";
                return;
            }

            if (!skillMatch) {
                showProfessionalApplyInvalidModal("You cannot apply outside your skill.");
                return;
            }

            // Show cover letter modal instead of direct apply
            window.pendingApplyJobId = Number(jobId);
            window.pendingApplyButton = button;
            const modal = document.getElementById("apply-cover-letter-modal");
            const textarea = document.getElementById("cover-letter-input");
            const countSpan = document.getElementById("cover-letter-count");
            
            if (textarea) textarea.value = "";
            if (countSpan) countSpan.textContent = "0";
            
            bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    });
}

function loadProfessionalIdentity() {
    const dashboardPhotoElement = document.getElementById("professional-dashboard-photo");
    const topbarPhotoElement = document.getElementById("professional-topbar-photo");

    function showProfileImageWhenReady(imageElement, rawUrl) {
        if (!imageElement) {
            return;
        }

        imageElement.style.display = "none";
        const fallbackUrl = "/images/user1.jpg";
        const sourceUrl = rawUrl || fallbackUrl;
        const withTimestamp =
            sourceUrl + (sourceUrl.includes("?") ? "&" : "?") + "t=" + Date.now();
        const loader = new Image();

        loader.onload = function () {
            imageElement.src = withTimestamp;
            imageElement.style.display = "block";
        };

        loader.onerror = function () {
            imageElement.src = fallbackUrl;
            imageElement.style.display = "block";
        };

        loader.src = withTimestamp;
    }

    return fetchJson("/api/pro/me")
        .then((profile) => {
            setText("professional-dashboard-name", profile.name || "Unavailable");
            setText("professional-sidebar-skill", profile.skill || "Unavailable");
            setText("professional-sidebar-location", profile.location || "Unavailable");
            
            const rate = Number(profile.rate ?? 0);
            const rateElement = document.getElementById("professional-sidebar-rate");
            if (rateElement) {
                if (rate > 0) {
                    let starsHtml = "";
                    for (let i = 1; i <= 5; i++) {
                        starsHtml += `<i class="fa-star ${i <= Math.round(rate) ? "fas text-warning" : "far text-secondary"}" style="font-size: 0.78rem;"></i>`;
                    }
                    rateElement.innerHTML = `
                        <span>${starsHtml}</span>
                        <span class="professional-rate-score">${rate.toFixed(1)}</span>
                    `;
                } else {
                    rateElement.innerHTML = '<span class="text-muted">Unavailable</span>';
                }
            }
            
            setProfessionalStatus(profile.approval_status);
            showProfileImageWhenReady(dashboardPhotoElement, profile.profile_photo);
            showProfileImageWhenReady(topbarPhotoElement, profile.profile_photo);
        })
        .catch(() => {
            setText("professional-dashboard-name", "Unavailable");
            setText("professional-sidebar-skill", "Unavailable");
            setText("professional-sidebar-location", "Unavailable");
            const rateElement = document.getElementById("professional-sidebar-rate");
            if (rateElement) {
                rateElement.innerHTML = '<span class="text-muted">Unavailable</span>';
            }
            setProfessionalStatus("pending");
            showProfileImageWhenReady(dashboardPhotoElement, null);
            showProfileImageWhenReady(topbarPhotoElement, null);
        });
}

function setProfessionalDashboardLoading(isLoading) {
    const loader = document.getElementById("professional-dashboard-loader");

    if (loader) {
        loader.classList.toggle("d-none", !isLoading);
    }
}

function renderProfessionalReputationContent(profile, reportsPayload) {
    const contentArea = getProfessionalContentArea();
    if (!contentArea) {
        return;
    }

    const reviews = Array.isArray(profile?.reviews) ? profile.reviews : [];
    const activeReports = Array.isArray(reportsPayload?.active_reports) ? reportsPayload.active_reports : [];
    const resolvedReports = Array.isArray(reportsPayload?.resolved_reports) ? reportsPayload.resolved_reports : [];
    const totalReports = Number(reportsPayload?.total_reports ?? activeReports.length + resolvedReports.length);
    const activeReportsCount = Number(reportsPayload?.active_reports_count ?? activeReports.length);
    const resolvedReportsCount = Number(reportsPayload?.resolved_reports_count ?? resolvedReports.length);

    const reviewsTable = reviews.length
        ? `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Contract Title</th>
                            <th>Client Name</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${reviews.map((review) => `
                            <tr>
                                <td><span class="badge text-bg-light border">${Number(review.rating || 0)}/5</span></td>
                                <td>${review.comment || "No comment"}</td>
                                <td>${review.contract_title || "Direct Request"}</td>
                                <td>${review.client_name || "N/A"}</td>
                                <td>${formatDate(review.created_at)}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `
        : '<div class="alert alert-light border mb-0">No reviews yet.</div>';

    const renderReportsRows = (items) => {
        if (!items.length) {
            return '<tr><td colspan="4" class="text-muted text-center py-3">No reports</td></tr>';
        }

        return items.map((report) => {
            const statusLabel = report?.status === "resolved" ? "Resolved" : "Under Review";
            const statusClass = report?.status === "resolved" ? "text-bg-success" : "text-bg-warning text-dark";
            const actionTakenRaw = String(report?.action_taken || "none");
            const actionTakenLabel = actionTakenRaw
                .split("_")
                .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
                .join(" ");
            const actionTakenClass = actionTakenRaw === "suspend_user"
                ? "text-bg-danger"
                : actionTakenRaw === "cancel_contract"
                    ? "text-bg-dark"
                    : actionTakenRaw === "warning"
                        ? "text-bg-warning text-dark"
                        : "text-bg-secondary";
            const rowClass = report?.status === "resolved" ? "table-success" : "";

            return `
                <tr class="${rowClass}">
                    <td>${report?.reason || "No reason provided."}</td>
                    <td><span class="badge ${statusClass}">${statusLabel}</span></td>
                    <td><span class="badge ${actionTakenClass}">${actionTakenLabel}</span></td>
                    <td>${formatDate(report?.created_at)}</td>
                </tr>
            `;
        }).join("");
    };

    contentArea.innerHTML = `
        <section class="professional-reputation-section">
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #dc3545 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <p class="text-muted small text-uppercase mb-1">Total Reports</p>
                                    <h4 class="mb-0 fw-bold">${totalReports}</h4>
                                </div>
                                ${activeReportsCount > 0 ? '<span class="badge text-bg-warning text-dark">Under Review</span>' : ""}
                            </div>
                            <p class="text-muted mb-0 mt-2 small">Active: ${activeReportsCount} | Resolved: ${resolvedReportsCount}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-flag me-2 text-danger"></i>Active Reports (${activeReportsCount})</h5>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Action Taken</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${renderReportsRows(activeReports)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-check-circle me-2 text-success"></i>Resolved Reports (${resolvedReportsCount})</h5>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Action Taken</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${renderReportsRows(resolvedReports)}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                <h4 class="fw-bold mb-0 text-info"><i class="fa-solid fa-star me-2"></i>Reviews</h4>
                <span class="badge text-bg-light border">Total: ${reviews.length}</span>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body" style="border-top: 4px solid #0dcaf0;">
                    ${reviewsTable}
                </div>
            </div>
        </section>
    `;
}

function loadProfessionalReputationView() {
    setActiveProfessionalNav("reviews-reports");
    clearProfessionalFeedback();
    setProfessionalContentHeader(
        "Reviews & Reports",
        "Your ratings and report summary visibility.",
        true
    );

    const contentArea = getProfessionalContentArea();
    if (contentArea) {
        contentArea.innerHTML = '<div class="text-muted">Loading reviews and reports...</div>';
    }

    const reloadButton = document.getElementById("professional-content-reload-button");
    if (reloadButton) {
        reloadButton.onclick = function () {
            loadProfessionalReputationView();
        };
    }

    return Promise.all([fetchJson("/api/pro/me"), fetchJson("/api/professional/my-reports")])
        .then(([profile, reportsPayload]) => {
            renderProfessionalReputationContent(profile, reportsPayload);
        })
        .catch(() => {
            if (contentArea) {
                contentArea.innerHTML = '<div class="alert alert-danger mb-0">Unable to load reviews and reports.</div>';
            }
        });
}

function loadProfessionalStats() {
    return fetchJson("/api/pro/stats")
        .then((payload) => {
            setText("pro-active-contracts-count", payload.active_contracts ?? 0);
            setText("pro-completed-jobs-count", payload.completed_jobs ?? 0);
            setText("pro-remaining-applies-count", payload.remaining_apply ?? 0);
            setText("pro-apply-limit-badge", `Monthly ${payload.monthly_limit ?? 0}`);
        })
        .catch(() => {
            setText("pro-active-contracts-count", "--");
            setText("pro-completed-jobs-count", "--");
            setText("pro-remaining-applies-count", "--");
            setText("pro-apply-limit-badge", "Monthly --");
        });
}

function setApplyPlanFeedback(type, message) {
    const feedback = document.getElementById("pro-apply-plan-feedback");
    if (!feedback) {
        return;
    }

    feedback.classList.remove("d-none");
    feedback.innerHTML = `<div class="alert alert-${type} mb-0">${message}</div>`;
}

function renderProfessionalTopPlanCard(state) {
    const totalRemaining = Number(state?.remaining_total ?? 0);
    const monthlyLimit = Number(state?.monthly_limit ?? 0);
    const periodEnd = state?.period_end || "";
    const extraRemaining = Number(state?.extra_remaining ?? 0);

    setText("pro-current-plan-name", state?.current_plan_name || "Free Plan");
    setText("pro-current-plan-jobs", `${totalRemaining}/${monthlyLimit}`);
    setText("pro-current-plan-duration", "Monthly");
    setText("pro-current-plan-expiry", periodEnd || "N/A");
    setText("pro-current-plan-requests", extraRemaining);
}

function renderApplyPlans(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container) {
        return;
    }

    if (!items.length) {
        container.innerHTML = '<div class="col-12 text-center text-white-50 py-3">No professional plans configured yet. Ask admin to add Pro plans.</div>';
        return;
    }

    container.innerHTML = items
        .map((plan, index) => {
            const isPopular = index === 1;
            const bgStyle = isPopular
                ? "background: rgba(138, 219, 174, 0.20); box-shadow: 0 8px 22px rgba(7, 19, 16, 0.14);"
                : "background: rgba(255, 255, 255, 0.12);";
            const planColor = isPopular ? "#effff4" : "#c8f0d6";
            const popularBadge = isPopular
                ? '<span class="small fw-bold px-2 py-1 rounded-pill" style="background: #e5f8ec; color: #1b4037;">Popular</span>'
                : "";
            const isMonthly = plan.plan_scope === "professional_monthly";
            const lineOne = isMonthly
                ? `${plan.apply_limit_monthly || 0} Applies / Month`
                : `${plan.extra_apply_quantity || 0} Extra Applies`;
            const lineTwo = isMonthly ? "Monthly Plan" : "One-time Extra Pack";

            return `
                <div class="col-12 col-md-4">
                    <div class="card border-0 rounded-4 h-100" style="${bgStyle}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-uppercase mb-0" style="color: ${planColor}; letter-spacing: 0.08em;">${plan.name || "Plan"}</h6>
                                ${popularBadge}
                            </div>
                            <h3 class="fw-bold text-white mb-0">Br${plan.price || 0}</h3>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${lineOne}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${lineTwo}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span></span>
                                <button type="button" class="btn btn-light btn-sm rounded-pill fw-semibold px-3 py-1 pro-buy-apply-plan-btn" data-plan-id="${plan.id}">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .join("");
}

function bindApplyPlanBuyButtons() {
    document.querySelectorAll(".pro-buy-apply-plan-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const planId = Number(button.dataset.planId);
            if (!Number.isInteger(planId) || planId <= 0 || professionalApplyPlanLoading) {
                return;
            }

            const originalText = button.innerHTML;
            professionalApplyPlanLoading = true;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';

            postJson(`/api/pro/apply-plans/${planId}/buy`, {})
                .then((payload) => {
                    if (payload?.redirect_url) {
                        window.location.href = payload.redirect_url;
                        return;
                    }

                    setApplyPlanFeedback("warning", "Payment link was not returned.");
                })
                .catch((error) => {
                    setApplyPlanFeedback("danger", error?.message || "Unable to start payment.");
                })
                .finally(() => {
                    professionalApplyPlanLoading = false;
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
        });
    });
}

function loadProfessionalApplyPlan() {
    const summary = document.getElementById("pro-apply-plan-summary");
    const current = document.getElementById("pro-apply-plan-current");
    const plansWrap = document.getElementById("pro-plans-container");

    if (!summary || !current || !plansWrap) {
        return Promise.resolve();
    }

    summary.textContent = "Loading apply plan...";
    plansWrap.innerHTML = '<div class="col-12 text-center text-white-50 py-3">Loading plans...</div>';

    return Promise.all([fetchJson("/api/pro/apply-plan"), fetchJson("/api/pro/apply-plans")])
        .then(([state, plansPayload]) => {
            const plans = toArray(plansPayload?.data);
            const sortedPlans = plans.sort((a, b) => Number(a.price || 0) - Number(b.price || 0));

            renderProfessionalTopPlanCard(state);
            current.textContent = state.current_plan_name || "Free Plan";
            summary.innerHTML = `
                <div class="small">
                    <strong>Remaining:</strong> ${state.remaining_total ?? 0}
                    (${state.monthly_remaining ?? 0} monthly + ${state.extra_remaining ?? 0} extra)
                    <span class="ms-2"><strong>Reset:</strong> ${state.period_end || "N/A"}</span>
                </div>
            `;

            renderApplyPlans("pro-plans-container", sortedPlans);
            bindApplyPlanBuyButtons();
        })
        .catch(() => {
            setText("pro-current-plan-name", "Plan Unavailable");
            setText("pro-current-plan-jobs", "--");
            setText("pro-current-plan-duration", "--");
            setText("pro-current-plan-expiry", "--");
            setText("pro-current-plan-requests", "--");
            current.textContent = "Unavailable";
            summary.innerHTML = '<div class="small text-warning">Unable to load apply plan status.</div>';
            plansWrap.innerHTML = '<div class="col-12 text-center text-white-50 py-3">Unavailable.</div>';
        });
}

function loadProfessionalJobs() {
    const skillInput = document.getElementById("pro-job-skill-search");
    const locationInput = document.getElementById("pro-job-location-search");
    const results = document.getElementById("professional-jobs-results");
    const params = new URLSearchParams();
    const skill = skillInput ? skillInput.value.trim() : "";
    const location = locationInput ? locationInput.value.trim() : "";

    if (skill) {
        params.set("skill", skill);
    }

    if (location) {
        params.set("location", location);
    }

    if (results) {
        results.innerHTML = '<div class="text-muted">Loading jobs...</div>';
    }

    return fetchJson(`/api/jobs${params.toString() ? `?${params.toString()}` : ""}`)
        .then((payload) => {
            renderProfessionalJobs(toArray(payload));
        })
        .catch(() => {
            renderProfessionalJobsError();
        });
}

function bindProfessionalSearch() {
    const skillInput = document.getElementById("pro-job-skill-search");
    const locationInput = document.getElementById("pro-job-location-search");
    const searchButton = document.getElementById("pro-job-search-button");
    const reloadButton = document.getElementById("professional-content-reload-button");

    if (!skillInput || !locationInput || !searchButton || !reloadButton) {
        return;
    }

    const triggerSearch = () => {
        clearProfessionalFeedback();
        loadProfessionalJobs();
    };

    skillInput.addEventListener("input", triggerSearch);
    locationInput.addEventListener("input", triggerSearch);
    searchButton.addEventListener("click", triggerSearch);
    reloadButton.addEventListener("click", triggerSearch);
}

function renderProfessionalApplicationsSection() {
    const contentArea = getProfessionalContentArea();

    if (!contentArea) {
        return;
    }

    setProfessionalContentHeader(
        "My Applications",
        "Track jobs you already applied to.",
        false
    );

    contentArea.innerHTML = `
        <section class="professional-applications-section">
            <div class="alert alert-light border mb-3">
                Pending applications show a <strong>Withdraw</strong> button. Anything else is read-only.
            </div>
            <div id="professional-applications-results">
                <div class="text-muted">Loading applications...</div>
            </div>
        </section>
    `;
}

function renderProfessionalApplications(applications) {
    const results = document.getElementById("professional-applications-results");

    if (!results) {
        return;
    }

    if (!applications.length) {
        results.innerHTML =
            '<div class="alert alert-light border mb-0">No applications found.</div>';
        return;
    }

    const rows = applications
        .map((application) => {
            const status = application.status || "pending";
            const isPending = status.toLowerCase() === "pending";
            const actionButton = isPending
                ? `
                    <button
                        type="button"
                        class="btn btn-danger w-100 professional-withdraw-application-button"
                        data-application-id="${application.id}"
                    >
                        Withdraw
                    </button>
                `
                : '<span class="text-muted small">No action</span>';

            return `
                <tr data-application-row-id="${application.id}">
                    <td class="fw-semibold">${application.job_title || "Untitled Job"}</td>
                    <td><span class="badge text-bg-light border">${status}</span></td>
                    <td class="text-end">${actionButton}</td>
                </tr>
            `;
        })
        .join("");

    results.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    bindProfessionalApplicationActions();
}

function renderProfessionalApplicationsError() {
    const results = document.getElementById("professional-applications-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load applications.</div>';
    }
}

function loadProfessionalApplications() {
    setActiveProfessionalNav("my-applications");
    clearProfessionalFeedback();
    renderProfessionalApplicationsSection();

    fetchJson("/api/pro/applications")
        .then((payload) => {
            renderProfessionalApplications(toArray(payload));
        })
        .catch(() => {
            renderProfessionalApplicationsError();
        });
}

function bindProfessionalApplicationActions() {
    document.querySelectorAll(".professional-withdraw-application-button").forEach((button) => {
        button.addEventListener("click", function () {
            const applicationId = button.dataset.applicationId;
            const row = button.closest("tr");

            if (!applicationId || button.disabled) {
                return;
            }

            button.disabled = true;
            button.textContent = "Withdrawing...";
            clearProfessionalFeedback();

            fetch("/api/pro/withdraw", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
                body: JSON.stringify({
                    id: Number(applicationId),
                }),
            })
                .then(async (response) => {
                    let payload = null;

                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok) {
                        throw new Error(payload?.message || "Unable to withdraw application.");
                    }

                    return payload;
                })
                .then((payload) => {
                    if (row) {
                        row.remove();
                    }

                    const tbody = document.querySelector("#professional-applications-results tbody");

                    if (tbody && !tbody.children.length) {
                        renderProfessionalApplications([]);
                    }

                    showProfessionalFeedback("success", "Application withdrawn successfully.");
                    loadProfessionalStats();
                    loadProfessionalApplyPlan();

                    if (typeof loadProfessionalJobs === "function") {
                        loadProfessionalJobs();
                    }
                })
                .catch((error) => {
                    button.disabled = false;
                    button.textContent = "Withdraw";
                    showProfessionalFeedback("danger", error.message || "Unable to withdraw application.");
                });
        });
    });
}

function renderProfessionalContractsSection() {
    const contentArea = getProfessionalContentArea();

    if (!contentArea) {
        return;
    }

    setProfessionalContentHeader(
        "My Contracts",
        "Review your active and completed contract history.",
        false
    );

    contentArea.innerHTML = `
        <section class="professional-contracts-section">
            <div class="alert alert-light border mb-3">
                Active contracts show a <strong>Complete</strong> button. Once submitted, they move to <strong>Pending Completion</strong> until the client confirms.
            </div>
            <div id="professional-contracts-results">
                <div class="text-muted">Loading contracts...</div>
            </div>
        </section>
    `;
}

function renderProfessionalContracts(contracts) {
    const results = document.getElementById("professional-contracts-results");

    if (!results) {
        return;
    }

    if (!contracts.length) {
        results.innerHTML =
            '<div class="alert alert-light border mb-0">No contracts found.</div>';
        return;
    }

    const rows = contracts
        .map((contract) => {
            const status = (contract.status || "active").toLowerCase();
            const isActive = status === "active";
            const isCompleted = status === "completed";
            const hasReview = Boolean(contract.has_review);
            const hasReport = Boolean(contract.has_report);
            const statusLabel = status === "completed"
                ? "Completed"
                : status === "pending_completion"
                    ? "Pending Completion"
                    : contract.status || "Active";
            let actionButton = '<span class="text-muted small">No action</span>';

            if (isActive) {
                actionButton = `
                    <div class="d-flex flex-column gap-2 align-items-end">
                        <button
                            type="button"
                            class="btn btn-success professional-complete-contract-button"
                            data-contract-id="${contract.id}"
                        >
                            Complete
                        </button>
                        ${hasReport
                            ? '<span class="badge bg-danger"><i class="fa-solid fa-flag me-1"></i>Reported</span>'
                            : `
                                <button
                                    type="button"
                                    class="btn btn-outline-danger professional-contract-feedback-button"
                                    data-contract-id="${contract.id}"
                                    data-mode="report"
                                >
                                    <i class="fa-solid fa-flag me-1"></i>Report Client
                                </button>
                            `}
                    </div>
                `;
            } else if (status === "pending_completion") {
                actionButton = '<span class="text-muted small">Waiting client confirmation</span>';
            } else if (isCompleted) {
                const badges = `
                    ${hasReview ? '<span class="badge bg-success"><i class="fa-solid fa-star me-1"></i>Rated</span>' : ''}
                    ${hasReport ? '<span class="badge bg-danger"><i class="fa-solid fa-flag me-1"></i>Reported</span>' : ''}
                `;
                const buttons = `
                    ${!hasReview
                        ? `
                            <button
                                type="button"
                                class="btn btn-outline-primary professional-contract-feedback-button"
                                data-contract-id="${contract.id}"
                                data-mode="rate"
                            >
                                <i class="fa-solid fa-star me-1"></i>Rate Client
                            </button>
                        `
                        : ''}
                    ${!hasReport
                        ? `
                            <button
                                type="button"
                                class="btn btn-outline-danger professional-contract-feedback-button"
                                data-contract-id="${contract.id}"
                                data-mode="report"
                            >
                                <i class="fa-solid fa-flag me-1"></i>Report Client
                            </button>
                        `
                        : ''}
                `;
                actionButton = `
                    <div class="d-flex flex-column gap-2 align-items-end">
                        ${badges}
                        ${buttons.trim() !== "" ? `<div class="d-flex flex-column gap-2 align-items-end">${buttons}</div>` : '<span class="text-muted small">Completed</span>'}
                    </div>
                `;
            }

            return `
                <tr>
                    <td class="fw-semibold">${contract.job_title || "Untitled Job"}</td>
                    <td>${contract.client_name || "N/A"}</td>
                    <td>${contract.client_phone || "N/A"}</td>
                    <td>${contract.professional_phone || "N/A"}</td>
                    <td>${formatPrice(contract.budget)}</td>
                    <td><span class="badge text-bg-light border">${statusLabel}</span></td>
                    <td>${formatDate(contract.created_at)}</td>
                    <td class="text-end">${actionButton}</td>
                </tr>
            `;
        })
        .join("");

    results.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Client Name</th>
                        <th scope="col">Client Phone</th>
                        <th scope="col">Professional Phone</th>
                        <th scope="col">Budget</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    bindProfessionalContractActions();
}

function renderProfessionalContractsError() {
    const results = document.getElementById("professional-contracts-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load contracts.</div>';
    }
}

function loadProfessionalContracts() {
    setActiveProfessionalNav("my-contracts");
    clearProfessionalFeedback();
    renderProfessionalContractsSection();

    fetchJson("/api/pro/contracts")
        .then((payload) => {
            renderProfessionalContracts(toArray(payload));
        })
        .catch(() => {
            renderProfessionalContractsError();
        });
}

function bindProfessionalContractActions() {
    document.querySelectorAll(".professional-complete-contract-button").forEach((button) => {
        button.addEventListener("click", function () {
            const contractId = button.dataset.contractId;
            const row = button.closest("tr");

            if (!contractId || button.disabled) {
                return;
            }

            button.disabled = true;
            button.textContent = "Completing...";
            clearProfessionalFeedback();

            fetch("/api/pro/complete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
                body: JSON.stringify({
                    id: Number(contractId),
                }),
            })
                .then(async (response) => {
                    let payload = null;

                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok) {
                        throw new Error(payload?.message || "Unable to complete contract.");
                    }

                    return payload;
                })
                .then(() => {
                    if (row) {
                        const statusBadge = row.querySelector(".badge");
                        const actionCell = row.lastElementChild;

                        if (statusBadge) {
                            statusBadge.textContent = "Pending Completion";
                        }

                        if (actionCell) {
                            actionCell.innerHTML = '<span class="text-muted small">Waiting client confirmation</span>';
                        }
                    }

                    showProfessionalFeedback("success", "Contract marked as pending completion.");
                    loadProfessionalStats();
                    loadProfessionalApplyPlan();
                })
                .catch((error) => {
                    button.disabled = false;
                    button.textContent = "Complete";
                    showProfessionalFeedback("danger", error.message || "Unable to complete contract.");
                });
        });
    });

    document.querySelectorAll(".professional-contract-feedback-button").forEach((button) => {
        button.addEventListener("click", function () {
            const contractId = button.dataset.contractId;
            const mode = button.dataset.mode || "report";

            if (!contractId) {
                return;
            }

            openProfessionalContractFeedbackModal(contractId, mode);
        });
    });
}

function ensureProfessionalContractFeedbackModal() {
    let modalElement = document.getElementById("professional-contract-feedback-modal");

    if (modalElement) {
        return modalElement;
    }

    const wrapper = document.createElement("div");
    wrapper.innerHTML = `
        <div class="modal fade" id="professional-contract-feedback-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="professional-contract-feedback-title">Rate / Report Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="professional-contract-feedback-form">
                        <div class="modal-body">
                            <input type="hidden" id="professional-contract-feedback-id">
                            <input type="hidden" id="professional-contract-feedback-mode" value="report">

                            <div class="mb-3" id="professional-rating-wrap">
                                <label for="professional-contract-rating" class="form-label fw-semibold">Rating</label>
                                <select id="professional-contract-rating" class="form-select">
                                    <option value="">Select rating</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Good</option>
                                    <option value="3">3 - Fair</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Bad</option>
                                </select>
                            </div>

                            <div class="mb-3" id="professional-review-wrap">
                                <label for="professional-contract-review" class="form-label fw-semibold">Review Comment (Optional)</label>
                                <textarea id="professional-contract-review" class="form-control" rows="3" placeholder="Write short review"></textarea>
                            </div>

                            <div class="mb-3" id="professional-report-reason-wrap">
                                <label for="professional-contract-report-reason" class="form-label fw-semibold">Reason</label>
                                <select id="professional-contract-report-reason" class="form-select">
                                    <option value="">Select reason</option>
                                    <option value="Late payment">Late payment</option>
                                    <option value="Abusive behavior">Abusive behavior</option>
                                    <option value="Scope manipulation">Scope manipulation</option>
                                    <option value="No communication">No communication</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3" id="professional-report-message-wrap">
                                <label for="professional-contract-report-message" class="form-label fw-semibold">Message (Optional)</label>
                                <textarea id="professional-contract-report-message" class="form-control" rows="3" placeholder="Additional details"></textarea>
                            </div>

                            <div id="professional-contract-feedback-status"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="professional-contract-feedback-submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(wrapper.firstElementChild);
    bindProfessionalContractFeedbackForm();
    return document.getElementById("professional-contract-feedback-modal");
}

function openProfessionalContractFeedbackModal(contractId, mode) {
    const modalElement = ensureProfessionalContractFeedbackModal();
    const idInput = document.getElementById("professional-contract-feedback-id");
    const modeInput = document.getElementById("professional-contract-feedback-mode");
    const titleEl = document.getElementById("professional-contract-feedback-title");
    const ratingWrap = document.getElementById("professional-rating-wrap");
    const reviewWrap = document.getElementById("professional-review-wrap");
    const reportReasonWrap = document.getElementById("professional-report-reason-wrap");
    const reportMessageWrap = document.getElementById("professional-report-message-wrap");
    const feedback = document.getElementById("professional-contract-feedback-status");
    const ratingInput = document.getElementById("professional-contract-rating");
    const reviewInput = document.getElementById("professional-contract-review");
    const reportReasonInput = document.getElementById("professional-contract-report-reason");
    const reportMessageInput = document.getElementById("professional-contract-report-message");
    const submitBtn = document.getElementById("professional-contract-feedback-submit");

    if (!modalElement || !idInput || !modeInput || !titleEl || !feedback || !submitBtn) {
        return;
    }

    const safeMode = mode === "rate" ? "rate" : "report";
    idInput.value = contractId;
    modeInput.value = safeMode;
    titleEl.textContent = safeMode === "rate" ? "Rate Client" : "Report Client";

    if (ratingInput) ratingInput.value = "";
    if (reviewInput) reviewInput.value = "";
    if (reportReasonInput) reportReasonInput.value = "";
    if (reportMessageInput) reportMessageInput.value = "";
    feedback.innerHTML = "";

    if (ratingWrap) ratingWrap.classList.toggle("d-none", safeMode !== "rate");
    if (reviewWrap) reviewWrap.classList.toggle("d-none", safeMode !== "rate");
    if (reportReasonWrap) reportReasonWrap.classList.toggle("d-none", safeMode !== "report");
    if (reportMessageWrap) reportMessageWrap.classList.toggle("d-none", safeMode !== "report");

    submitBtn.className = safeMode === "rate" ? "btn btn-primary" : "btn btn-danger";
    submitBtn.textContent = safeMode === "rate" ? "Submit Rating" : "Submit Report";

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function bindProfessionalContractFeedbackForm() {
    const form = document.getElementById("professional-contract-feedback-form");
    if (!form) {
        return;
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const contractId = document.getElementById("professional-contract-feedback-id")?.value;
        const mode = document.getElementById("professional-contract-feedback-mode")?.value || "report";
        const rating = document.getElementById("professional-contract-rating")?.value || "";
        const comment = document.getElementById("professional-contract-review")?.value?.trim() || "";
        const reason = document.getElementById("professional-contract-report-reason")?.value || "";
        const message = document.getElementById("professional-contract-report-message")?.value?.trim() || "";
        const feedback = document.getElementById("professional-contract-feedback-status");
        const submitBtn = document.getElementById("professional-contract-feedback-submit");
        const modalElement = document.getElementById("professional-contract-feedback-modal");

        if (!contractId || !feedback || !submitBtn || !modalElement) {
            return;
        }

        if (mode === "rate" && !rating) {
            feedback.innerHTML = '<div class="alert alert-warning mb-0">Please select rating.</div>';
            return;
        }

        if (mode === "report" && !reason) {
            feedback.innerHTML = '<div class="alert alert-warning mb-0">Please select report reason.</div>';
            return;
        }

        submitBtn.disabled = true;
        feedback.innerHTML = '<div class="text-muted">Submitting...</div>';

        const request = mode === "rate"
            ? postJson(`/api/contracts/${contractId}/review`, { rating: Number(rating), comment })
            : postJson(`/api/contracts/${contractId}/report`, { reason, message });

        request
            .then(() => {
                feedback.innerHTML = `<div class="alert alert-success mb-0">${mode === "rate" ? "Rating submitted." : "Report submitted."}</div>`;
                loadProfessionalContracts();
                setTimeout(() => {
                    bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                }, 500);
            })
            .catch((error) => {
                feedback.innerHTML = `<div class="alert alert-danger mb-0">${error?.message || "Unable to submit."}</div>`;
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
    });
}

function bindProfessionalSidebarNavigation() {
    document.querySelectorAll(".professional-nav-button").forEach((button) => {
        button.addEventListener("click", function () {
            const view = button.dataset.view;

            if (view === "my-contracts") {
                loadProfessionalContracts();
                return;
            }

            if (view === "my-applications") {
                loadProfessionalApplications();
                return;
            }

            if (view === "direct-requests") {
                loadDirectRequests();
                return;
            }

            if (view === "reviews-reports") {
                loadProfessionalReputationView();
                return;
            }

            if (view === "portfolio") {
                loadProfessionalPortfolioView();
                return;
            }

            loadProfessionalJobsView();
        });
    });
}

function loadProfessionalProfileForSettings() {
    fetchJson("/api/pro/profile")
        .then((data) => {
            if (data.success && data.data) {
                const d = data.data;
                document.getElementById("professional-profile-name").value = d.name || "";
                document.getElementById("professional-profile-email").value = d.email || "";
                document.getElementById("professional-profile-phone").value = d.phone || "";
                document.getElementById("professional-profile-location").value = d.location || "";
                
                const photoPreview = document.getElementById("professional-settings-profile-preview");
                if (photoPreview && d.profile_photo) {
                    photoPreview.src = d.profile_photo;
                } else if (photoPreview) {
                    photoPreview.src = "/images/user1.jpg";
                }
            }
        })
        .catch((err) => {
            console.error("Error loading professional profile:", err);
        });
}

function bindProfessionalSettings() {
    const settingsButton = document.getElementById("professional-settings-button");
    const settingsModalElement = document.getElementById("professional-settings-modal");
    const deleteAccountModalElement = document.getElementById("professional-delete-account-modal");
    const darkModeToggle = document.getElementById("professional-dark-mode-toggle");
    const topbarDarkModeToggle = document.getElementById("professional-topbar-dark-mode");
    const darkModeLabel = document.getElementById("professional-dark-mode-label");
    const deleteAccountButton = document.getElementById("professional-delete-account-button");
    const confirmDeleteAccountButton = document.getElementById("professional-confirm-delete-account-button");
    const settingsModal = settingsModalElement
        ? bootstrap.Modal.getOrCreateInstance(settingsModalElement)
        : null;
    const deleteAccountModal = deleteAccountModalElement
        ? bootstrap.Modal.getOrCreateInstance(deleteAccountModalElement)
        : null;

    // Load profile when modal is shown
    if (settingsModalElement) {
        settingsModalElement.addEventListener("shown.bs.modal", function() {
            loadProfessionalProfileForSettings();
        });
    }

    // Edit field buttons
    const editButtons = document.querySelectorAll(".edit-field-btn");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            const fieldId = this.getAttribute("data-field");
            const input = document.getElementById(fieldId);
            if (input) {
                input.removeAttribute("readonly");
                input.classList.remove("bg-light");
                input.focus();
                input.addEventListener("blur", function() {
                    input.setAttribute("readonly", true);
                    input.classList.add("bg-light");
                }, { once: true });
            }
        });
    });

    // Save button
    const saveBtn = document.getElementById("professional-save-profile-btn");
    const photoInput = document.getElementById("professional-settings-photo-input");
    
    if (photoInput) {
        photoInput.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById("professional-settings-profile-preview");
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener("click", function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append("name", document.getElementById("professional-profile-name").value);
            formData.append("email", document.getElementById("professional-profile-email").value);
            formData.append("phone", document.getElementById("professional-profile-phone").value);
            formData.append("location", document.getElementById("professional-profile-location").value);

            if (photoInput && photoInput.files.length > 0) {
                formData.append("profile_photo", photoInput.files[0]);
            }

            const originalText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch("/api/pro/profile-update-simple", {
                method: "POST",
                headers: buildHeaders(),
                body: formData,
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    alert("Profile updated successfully!");
                    
                    // Update dashboard and topbar photos if photo was changed
                    if (photoInput && photoInput.files.length > 0 && data.data && data.data.profile_photo) {
                        const timestamp = new Date().getTime();
                        const newPhoto = data.data.profile_photo + '?t=' + timestamp;
                        
                        const dashboardPhoto = document.getElementById("professional-dashboard-photo");
                        if (dashboardPhoto) {
                            dashboardPhoto.src = newPhoto;
                        }
                        
                        const topbarPhoto = document.getElementById("professional-topbar-photo");
                        if (topbarPhoto) {
                            topbarPhoto.src = newPhoto;
                        }
                    }
                    
                    loadProfessionalIdentity();
                    loadProfessionalProfileForSettings();
                } else {
                    alert(data.message || "Failed to update profile");
                }
            })
            .catch(() => {
                alert("Failed to update profile. Please try again.");
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
        });
    }

    function syncProfessionalDarkModeLabel() {
        if (!darkModeLabel) {
            return;
        }

        darkModeLabel.textContent = document.body.classList.contains("professional-dashboard-dark")
            ? "Disable"
            : "Enable";
    }

    if (localStorage.getItem("professional_dashboard_theme") === "dark") {
        document.body.classList.add("professional-dashboard-dark");
    }

    syncProfessionalDarkModeLabel();

    if (settingsButton) {
        settingsButton.addEventListener("click", function () {
            if (settingsModal) {
                settingsModal.show();
            }
        });
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener("click", function () {
            document.body.classList.toggle("professional-dashboard-dark");

            if (document.body.classList.contains("professional-dashboard-dark")) {
                localStorage.setItem("professional_dashboard_theme", "dark");
            } else {
                localStorage.removeItem("professional_dashboard_theme");
            }

            syncProfessionalDarkModeLabel();
        });
    }

    if (topbarDarkModeToggle) {
        topbarDarkModeToggle.addEventListener("click", function () {
            document.body.classList.toggle("professional-dashboard-dark");

            if (document.body.classList.contains("professional-dashboard-dark")) {
                localStorage.setItem("professional_dashboard_theme", "dark");
            } else {
                localStorage.removeItem("professional_dashboard_theme");
            }

            syncProfessionalDarkModeLabel();
        });
    }

    if (deleteAccountButton) {
        deleteAccountButton.addEventListener("click", function () {
            if (settingsModal) {
                settingsModal.hide();
            }

            if (deleteAccountModal) {
                deleteAccountModal.show();
            }
        });
    }

    if (confirmDeleteAccountButton) {
        confirmDeleteAccountButton.addEventListener("click", function () {
            confirmDeleteAccountButton.disabled = true;
            confirmDeleteAccountButton.textContent = "Deleting...";

            deleteJson("/api/account")
                .then(() => {
                    localStorage.removeItem("token");
                    localStorage.removeItem("role");
                    localStorage.removeItem("professional_dashboard_theme");
                    window.location.href = "/";
                })
                .catch((error) => {
                    window.alert(error.message || "Failed to delete account.");
                })
                .finally(() => {
                    confirmDeleteAccountButton.disabled = false;
                    confirmDeleteAccountButton.textContent = "Yes";
                    if (deleteAccountModal) {
                        deleteAccountModal.hide();
                    }
                });
        });
    }
}

function loadDirectRequests() {
    setActiveProfessionalNav("direct-requests");
    clearProfessionalFeedback();
    
    const contentArea = document.getElementById("professional-content-area") || document.getElementById("content-area");
    if (!contentArea) return;
    
    contentArea.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading requests...</p>
        </div>
    `;
    
    fetchJson("/api/pro/requests")
        .then((requests) => {
            renderDirectRequests(requests);
        })
        .catch((err) => {
            if (contentArea) {
                contentArea.innerHTML = '<div class="alert alert-danger mb-0">Failed to load requests.</div>';
            }
        });
}

function renderDirectRequests(requests) {
    const contentArea = document.getElementById("professional-content-area") || document.getElementById("content-area");
    if (!contentArea) return;
    
    setProfessionalContentHeader("Direct Requests", "Incoming job requests from clients", false);
    
    if (!requests || requests.length === 0) {
        contentArea.innerHTML = '<div class="alert alert-light border mb-0">No requests yet.</div>';
        return;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }
    
    const rows = requests.map((req) => {
        const statusBadge = req.status === 'pending' 
            ? '<span class="badge bg-warning text-dark">Pending</span>'
            : req.status === 'accepted'
                ? '<span class="badge bg-success">Accepted</span>'
                : req.status === 'expired'
                    ? '<span class="badge bg-secondary">Expired</span>'
                    : '<span class="badge bg-danger">Rejected</span>';
        
        const actionButtons = req.status === 'pending' 
            ? `<div class="d-flex gap-2">
                <button class="btn btn-sm btn-success" onclick="acceptDirectRequest(${req.id}, '${escapeHtml(req.title)}', '${escapeHtml(req.client?.name)}', ${req.budget || 0})">
                    <i class="fa-solid fa-check me-1"></i> Accept
                </button>
                <button class="btn btn-sm btn-danger" onclick="rejectDirectRequest(${req.id}, '${escapeHtml(req.title)}', '${escapeHtml(req.client?.name)}', ${req.budget || 0})">
                    <i class="fa-solid fa-times me-1"></i> Reject
                </button>
            </div>`
            : '<span class="text-muted small">No action available</span>';
        
        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">${req.title || 'Untitled'}</h6>
                        ${statusBadge}
                    </div>
                    <p class="text-muted mb-2">${req.description || 'No description'}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">From: </span>
                            <span class="fw-semibold">${req.client?.name || 'Unknown'}</span>
                            ${req.budget ? `<span class="badge bg-success ms-2">Br${req.budget}</span>` : ''}
                        </div>
                        ${actionButtons}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    contentArea.innerHTML = rows;
}

window.acceptDirectRequest = function(id, title, clientName, budget) {
    const modal = document.getElementById('direct-request-action-modal');
    if (modal) bootstrap.Modal.getInstance(modal)?.hide();
    
    setTimeout(function() {
        const header = document.getElementById('action-modal-header');
        const titleEl = document.getElementById('action-modal-title');
        const iconEl = document.getElementById('action-modal-icon');
        const requestTitle = document.getElementById('action-modal-request-title');
        const clientNameEl = document.getElementById('action-modal-client-name');
        const budgetEl = document.getElementById('action-modal-budget');
        const messageEl = document.getElementById('action-modal-message');
        const confirmBtn = document.getElementById('confirm-action-btn');

        // Reset and set Accept styling
        header.className = 'modal-header bg-success text-white border-0';
        titleEl.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>Accept Request';
        iconEl.innerHTML = '<i class="fa-solid fa-handshake fa-4x text-success"></i>';
        requestTitle.textContent = title || 'Untitled Request';
        clientNameEl.textContent = 'From: ' + (clientName || 'Unknown Client');
        budgetEl.textContent = budget ? 'Br ' + budget : '';
        budgetEl.style.display = budget ? 'inline-block' : 'none';
        messageEl.textContent = 'A contract will be created with this client. Are you sure?';

        confirmBtn.className = 'btn btn-success px-4';
        confirmBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Accept';

        confirmBtn.onclick = function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Accepting...';

            fetch(`/api/pro/requests/${id}/accept`, {
                method: 'POST',
                headers: { 
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                }
            })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }

                if (!res.ok || data.success === false) {
                    throw new Error(data.message || 'Unable to accept request.');
                }

                return data;
            })
            .then(data => {
                bootstrap.Modal.getInstance(modal).hide();
                const contentArea = document.getElementById("professional-content-area") || document.getElementById("content-area");
                if (contentArea) {
                    contentArea.innerHTML = '<div class="alert alert-success mb-3"><i class="fa-solid fa-check-circle me-2"></i>Request accepted! Contract created.</div>' + contentArea.innerHTML;
                }
                loadDirectRequests();
                loadProfessionalStats();
                loadProfessionalApplyPlan();
            })
            .catch((err) => {
                const messageEl = document.getElementById('action-modal-message');
                if (messageEl) {
                    messageEl.innerHTML = '<div class="alert alert-warning mb-0"><i class="fa-solid fa-exclamation-triangle me-2"></i>' + (err?.message || 'Unable to accept request.') + '</div>';
                }
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Accept';
            });
        };

        bootstrap.Modal.getOrCreateInstance(modal).show();
    }, 100);
};

window.rejectDirectRequest = function(id, title, clientName, budget) {
    const modal = document.getElementById('direct-request-action-modal');
    if (modal) bootstrap.Modal.getInstance(modal)?.hide();
    
    setTimeout(function() {
        const header = document.getElementById('action-modal-header');
        const titleEl = document.getElementById('action-modal-title');
        const iconEl = document.getElementById('action-modal-icon');
        const requestTitle = document.getElementById('action-modal-request-title');
        const clientNameEl = document.getElementById('action-modal-client-name');
        const budgetEl = document.getElementById('action-modal-budget');
        const messageEl = document.getElementById('action-modal-message');
        const confirmBtn = document.getElementById('confirm-action-btn');

        header.className = 'modal-header bg-danger text-white border-0';
        titleEl.innerHTML = '<i class="fa-solid fa-times-circle me-2"></i>Reject Request';
        iconEl.innerHTML = '<i class="fa-solid fa-times-circle fa-4x text-danger"></i>';
        requestTitle.textContent = title || 'Untitled Request';
        clientNameEl.textContent = 'From: ' + (clientName || 'Unknown Client');
        budgetEl.textContent = budget ? 'Br ' + budget : '';
        budgetEl.style.display = budget ? 'inline-block' : 'none';
        messageEl.textContent = 'This request will be rejected. Are you sure?';

        confirmBtn.className = 'btn btn-danger px-4';
        confirmBtn.innerHTML = '<i class="fa-solid fa-times me-1"></i>Reject';

        confirmBtn.onclick = function() {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...';

            fetch(`/api/pro/requests/${id}/reject`, {
                method: 'POST',
                headers: { 
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json'
                }
            })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }

                if (!res.ok) {
                    throw new Error(data.message || 'Unable to reject request.');
                }

                return data;
            })
            .then(() => {
                bootstrap.Modal.getInstance(modal).hide();
                const contentArea = document.getElementById("professional-content-area") || document.getElementById("content-area");
                if (contentArea) {
                    contentArea.innerHTML = '<div class="alert alert-success mb-3"><i class="fa-solid fa-check-circle me-2"></i>Request rejected successfully.</div>' + contentArea.innerHTML;
                }
                loadDirectRequests();
            })
            .catch((err) => {
                const messageEl = document.getElementById('action-modal-message');
                if (messageEl) {
                    messageEl.innerHTML = '<div class="alert alert-warning mb-0"><i class="fa-solid fa-exclamation-triangle me-2"></i>' + (err?.message || 'Unable to reject request.') + '</div>';
                }
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fa-solid fa-times me-1"></i>Reject';
            });
        };

        bootstrap.Modal.getOrCreateInstance(modal).show();
    }, 100);
};

function ensurePortfolioEditModal() {
    let modalElement = document.getElementById("professional-portfolio-edit-modal");
    if (modalElement) {
        return modalElement;
    }

    const wrapper = document.createElement("div");
    wrapper.innerHTML = `
        <div class="modal fade" id="professional-portfolio-edit-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Portfolio Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="professional-portfolio-edit-form">
                        <div class="modal-body">
                            <input type="hidden" id="portfolio-edit-id">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" id="portfolio-edit-description" rows="3" maxlength="500" placeholder="Describe this work..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Linked Job ID</label>
                                <input type="number" class="form-control" id="portfolio-edit-job-id" min="1" placeholder="Optional">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Replace Image</label>
                                <input type="file" class="form-control" id="portfolio-edit-image" accept="image/*">
                            </div>
                            <div id="portfolio-edit-feedback" class="mt-3"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="portfolio-edit-save-btn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(wrapper.firstElementChild);

    const form = document.getElementById("professional-portfolio-edit-form");
    if (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const itemId = document.getElementById("portfolio-edit-id")?.value;
            const description = document.getElementById("portfolio-edit-description")?.value ?? "";
            const jobIdRaw = document.getElementById("portfolio-edit-job-id")?.value ?? "";
            const imageInput = document.getElementById("portfolio-edit-image");
            const feedback = document.getElementById("portfolio-edit-feedback");
            const saveBtn = document.getElementById("portfolio-edit-save-btn");

            if (!itemId || !saveBtn || !feedback) {
                return;
            }

            const formData = new FormData();
            formData.append("_method", "PUT");
            formData.append("description", description);
            formData.append("linked_job_id", jobIdRaw);
            if (imageInput && imageInput.files && imageInput.files[0]) {
                formData.append("image", imageInput.files[0]);
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            feedback.innerHTML = "";

            fetch(`/api/pro/portfolio/${itemId}`, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
                body: formData,
            })
                .then(async (response) => {
                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok || !payload?.success) {
                        throw new Error(payload?.message || "Unable to update portfolio item.");
                    }
                    return payload;
                })
                .then(() => {
                    feedback.innerHTML = '<div class="alert alert-success mb-0">Portfolio item updated.</div>';
                    setTimeout(() => {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById("professional-portfolio-edit-modal")).hide();
                        loadProfessionalPortfolioView();
                    }, 500);
                })
                .catch((error) => {
                    feedback.innerHTML = `<div class="alert alert-danger mb-0">${error.message || "Unable to update portfolio item."}</div>`;
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = "Save";
                });
        });
    }

    return document.getElementById("professional-portfolio-edit-modal");
}

function openPortfolioEditModal(item) {
    const modalElement = ensurePortfolioEditModal();
    const idInput = document.getElementById("portfolio-edit-id");
    const descriptionInput = document.getElementById("portfolio-edit-description");
    const jobIdInput = document.getElementById("portfolio-edit-job-id");
    const imageInput = document.getElementById("portfolio-edit-image");
    const feedback = document.getElementById("portfolio-edit-feedback");
    const saveBtn = document.getElementById("portfolio-edit-save-btn");

    if (!modalElement || !idInput || !descriptionInput || !jobIdInput || !feedback || !saveBtn) {
        return;
    }

    idInput.value = item.id;
    descriptionInput.value = item.description || "";
    jobIdInput.value = item.linked_job_id || "";
    if (imageInput) imageInput.value = "";
    feedback.innerHTML = "";
    saveBtn.disabled = false;
    saveBtn.innerHTML = "Save";

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function bindPortfolioActions(items) {
    const createForm = document.getElementById("professional-portfolio-create-form");
    const createFeedback = document.getElementById("professional-portfolio-create-feedback");

    if (createForm) {
        createForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const imageInput = document.getElementById("professional-portfolio-create-image");
            const descriptionInput = document.getElementById("professional-portfolio-create-description");
            const jobInput = document.getElementById("professional-portfolio-create-job-id");
            const submitBtn = document.getElementById("professional-portfolio-create-btn");

            if (!imageInput || !submitBtn) {
                return;
            }

            if (!imageInput.files || !imageInput.files[0]) {
                if (createFeedback) {
                    createFeedback.innerHTML = '<div class="alert alert-warning mb-0">Please select an image.</div>';
                }
                return;
            }

            const formData = new FormData();
            formData.append("image", imageInput.files[0]);
            formData.append("description", descriptionInput?.value ?? "");
            formData.append("linked_job_id", jobInput?.value ?? "");

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
            if (createFeedback) createFeedback.innerHTML = "";

            fetch("/api/pro/portfolio", {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
                body: formData,
            })
                .then(async (response) => {
                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok || !payload?.success) {
                        throw new Error(payload?.message || "Unable to upload portfolio item.");
                    }
                    return payload;
                })
                .then(() => {
                    if (createFeedback) {
                        createFeedback.innerHTML = '<div class="alert alert-success mb-0">Portfolio item uploaded.</div>';
                    }
                    loadProfessionalPortfolioView();
                })
                .catch((error) => {
                    if (createFeedback) {
                        createFeedback.innerHTML = `<div class="alert alert-danger mb-0">${error.message || "Unable to upload portfolio item."}</div>`;
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-upload me-1"></i>Add Item';
                });
        });
    }

    document.querySelectorAll(".professional-portfolio-edit-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const itemId = Number(button.dataset.itemId);
            const item = items.find((entry) => Number(entry.id) === itemId);
            if (!item) return;
            openPortfolioEditModal(item);
        });
    });

    document.querySelectorAll(".professional-portfolio-delete-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const itemId = Number(button.dataset.itemId);
            if (!itemId) return;
            if (!window.confirm("Delete this portfolio item?")) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`/api/pro/portfolio/${itemId}`, {
                method: "DELETE",
                headers: {
                    "Accept": "application/json",
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                },
            })
                .then(async (response) => {
                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok || !payload?.success) {
                        throw new Error(payload?.message || "Unable to delete portfolio item.");
                    }
                })
                .then(() => {
                    loadProfessionalPortfolioView();
                })
                .catch((error) => {
                    window.alert(error.message || "Unable to delete portfolio item.");
                    button.disabled = false;
                    button.innerHTML = '<i class="fa-solid fa-trash me-1"></i>Delete';
                });
        });
    });
}

function renderProfessionalPortfolio(items) {
    const contentArea = getProfessionalContentArea();
    if (!contentArea) return;

    const cards = items.length ? items.map((item) => `
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="${item.image_url || "/images/user1.jpg"}" class="card-img-top" alt="Portfolio image" style="height: 180px; object-fit: cover;">
                <div class="card-body">
                    <p class="mb-2">${item.description || "No description"}</p>
                    <p class="text-muted small mb-2">Linked Job: ${item.linked_job_id || "N/A"}</p>
                    <p class="text-muted small mb-3">Added: ${formatDate(item.created_at)}</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary professional-portfolio-edit-btn" data-item-id="${item.id}">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger professional-portfolio-delete-btn" data-item-id="${item.id}">
                            <i class="fa-solid fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join("") : '<div class="alert alert-light border mb-0">No portfolio items yet.</div>';

    contentArea.innerHTML = `
        <section class="professional-portfolio-section">
            <div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid #20c997 !important;">
                <div class="card-body">
                    <form id="professional-portfolio-create-form">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="file" class="form-control" id="professional-portfolio-create-image" accept="image/*" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="professional-portfolio-create-description" placeholder="Description (optional)">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" id="professional-portfolio-create-job-id" min="1" placeholder="Job ID">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-success" id="professional-portfolio-create-btn">
                                    <i class="fa-solid fa-upload me-1"></i>Add Item
                                </button>
                            </div>
                        </div>
                        <div id="professional-portfolio-create-feedback" class="mt-2"></div>
                    </form>
                </div>
            </div>

            ${items.length ? `<div class="row g-3">${cards}</div>` : cards}
        </section>
    `;

    bindPortfolioActions(items);
}

function loadProfessionalPortfolioView() {
    setActiveProfessionalNav("portfolio");
    clearProfessionalFeedback();
    setProfessionalContentHeader(
        "Portfolio",
        "Create, update, and delete your portfolio items.",
        true
    );

    const contentArea = getProfessionalContentArea();
    if (contentArea) {
        contentArea.innerHTML = '<div class="text-muted">Loading portfolio...</div>';
    }

    const reloadButton = document.getElementById("professional-content-reload-button");
    if (reloadButton) {
        reloadButton.onclick = function () {
            loadProfessionalPortfolioView();
        };
    }

    return fetchJson("/api/pro/portfolio")
        .then((payload) => {
            const items = Array.isArray(payload?.data) ? payload.data : [];
            renderProfessionalPortfolio(items);
        })
        .catch(() => {
            if (contentArea) {
                contentArea.innerHTML = '<div class="alert alert-danger mb-0">Unable to load portfolio.</div>';
            }
        });
}

function loadProfessionalJobsView() {
    setActiveProfessionalNav("browse-jobs");
    clearProfessionalFeedback();
    renderProfessionalJobsSection();
    return loadProfessionalJobs();
}

function initializeProfessionalDashboard() {
    setProfessionalDashboardLoading(true);
    bindProfessionalSidebarNavigation();
    bindProfessionalSettings();

    Promise.allSettled([
        loadProfessionalStats(),
        loadProfessionalJobsView(),
        loadProfessionalApplyPlan(),
        loadProfessionalIdentity(),
    ]).finally(() => {
        setProfessionalDashboardLoading(false);
    });
}


    // Cover letter application form bindings.
    function bindProfessionalCoverLetterForm() {
const coverLetterForm = document.getElementById("apply-cover-letter-form");
const coverLetterInput = document.getElementById("cover-letter-input");
const coverLetterCount = document.getElementById("cover-letter-count");
const submitBtn = document.getElementById("submit-cover-letter-btn");

if (coverLetterInput && coverLetterCount) {
    coverLetterInput.addEventListener("input", function() {
        coverLetterCount.textContent = this.value.length;
    });
}

if (coverLetterForm && submitBtn) {
    coverLetterForm.addEventListener("submit", function(e) {
        e.preventDefault();
        
        const jobId = Number(window.pendingApplyJobId);
        const button = window.pendingApplyButton;
        const coverLetter = coverLetterInput.value.trim();

        if (!Number.isInteger(jobId) || jobId <= 0) {
            showProfessionalApplyInvalidModal("Invalid job selection. Please reopen the job and try again.");
            return;
        }
        
        if (!coverLetter || coverLetter.length < 20) {
            alert("Please write a cover letter (at least 20 characters).");
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
        
        postJson(`/api/jobs/${jobId}/apply`, {
            cover_letter: coverLetter,
        })
            .then(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById("apply-cover-letter-modal")).hide();
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Submit Application';
                
                if (button) {
                    button.className = "btn btn-secondary professional-apply-button";
                    button.dataset.hasApplied = "true";
                    button.textContent = "Applied";
                    button.disabled = true;
                }
                
                showProfessionalFeedback("success", "Application submitted successfully.");
                loadProfessionalStats();
                loadProfessionalApplyPlan();
                window.pendingApplyJobId = null;
                window.pendingApplyButton = null;
            })
            .catch((error) => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Submit Application';
                const message = error.message || "Unable to apply for this job.";
                showProfessionalApplyInvalidModal(message);
            });
    });
}
    }

    // Professional dashboard entrypoint (called after DOM is ready).
    function init() {
        initializeProfessionalDashboard();
        bindProfessionalCoverLetterForm();
    }

    window.EasyHireProfessional = { init };

    // Required globals for existing inline onclick handlers and links.
    window.loadProfessionalJobs = loadProfessionalJobs;
    window.bindProfessionalSearch = bindProfessionalSearch;
    window.loadProfessionalStats = loadProfessionalStats;
})();
});
