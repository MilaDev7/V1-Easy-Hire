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
            <div class="row g-3 mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%); border: 1px solid rgba(0,0,0,0.05);">
                <div class="col-md-6">
                    <label for="pro-job-skill-search" class="form-label small text-uppercase text-muted mb-2">Skill</label>
                    <input
                        type="text"
                        id="pro-job-skill-search"
                        class="form-control"
                        placeholder="Search by skill"
                    >
                </div>
                <div class="col-md-4">
                    <label for="pro-job-location-search" class="form-label small text-uppercase text-muted mb-2">Location</label>
                    <input
                        type="text"
                        id="pro-job-location-search"
                        class="form-control"
                        placeholder="Search by location"
                    >
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button
                        type="button"
                        id="pro-job-search-button"
                        class="btn btn-dark w-100"
                    >
                        Search
                    </button>
                </div>
            </div>

            <div id="professional-jobs-results">
                <div class="text-muted">Loading jobs...</div>
            </div>
        </section>
    `;

    bindProfessionalSearch();
}

function renderProfessionalJobs(jobs) {
    const results = document.getElementById("professional-jobs-results");

    if (!results) {
        return;
    }

    if (!jobs.length) {
        results.innerHTML =
            '<div class="alert alert-light border mb-0">No open jobs matched your filters.</div>';
        return;
    }

    const cards = jobs
        .map((job) => {
            const skills = job.skills || job.skill || "N/A";
            const status = job.status || "open";
            const location = job.location || "N/A";
            const alreadyApplied = Boolean(job.has_applied);
            const skillMatch = Boolean(job.skill_match);
            const buttonClass = alreadyApplied ? "btn btn-secondary" : "btn btn-dark";
            const buttonText = alreadyApplied ? "Applied" : "Apply";
            const startDate = job.start_date ? formatDate(job.start_date) : null;
            const deadline = job.deadline ? formatDate(job.deadline) : null;
            
            const dateBadges = [];
            if (startDate) dateBadges.push(`<span class="badge text-bg-light border">Start: ${startDate}</span>`);
            if (deadline) dateBadges.push(`<span class="badge text-bg-light border">Deadline: ${deadline}</span>`);

            return `
                <article class="card professional-job-card shadow-sm h-100" style="border-left: 4px solid #20c997 !important;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="h5 mb-2">${job.title || "Untitled Job"}</h3>
                                <p class="professional-job-description mb-0">${shortText(job.description)}</p>
                            </div>
                            <div class="text-lg-end">
                                <p class="text-muted small text-uppercase mb-1">Budget</p>
                                <p class="h5 mb-0">${formatPrice(job.budget)}</p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="professional-job-skills">
                                <i class="fa-solid fa-sparkles"></i> ${skills}
                            </span>
                            <span class="badge text-bg-light border">Status: ${status}</span>
                            <span class="badge text-bg-light border">Location: ${location}</span>
                            <span class="badge ${skillMatch ? "text-bg-success" : "text-bg-warning"}">
                                ${skillMatch ? "Skill Match" : "Skill Check"}
                            </span>
                        </div>
                        
                        ${dateBadges.length > 0 ? `<div class="d-flex flex-wrap gap-2 mb-3">${dateBadges.join('')}</div>` : ''}

                        <div class="professional-job-meta text-muted mb-4">
                            Frontend placeholder match check is currently based on your primary skill and the job skill.
                        </div>

                        <div class="mt-auto d-flex justify-content-end">
                            <button
                                type="button"
                                class="${buttonClass} professional-apply-button"
                                data-job-id="${job.id}"
                                data-has-applied="${alreadyApplied ? "true" : "false"}"
                                data-skill-match="${skillMatch ? "true" : "false"}"
                                ${alreadyApplied ? "disabled" : ""}
                            >
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                </article>
            `;
        })
        .join("");

    results.innerHTML = `<div class="row g-3">${cards}</div>`;
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
                        starsHtml += `<i class="fa-star ${i <= Math.round(rate) ? 'fas text-warning' : 'far text-secondary'}"></i>`;
                    }
                    starsHtml += `<span class="ms-1 text-muted small">${rate.toFixed(1)}</span>`;
                    rateElement.innerHTML = starsHtml;
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

function loadProfessionalStats() {
    return fetchJson("/api/pro/stats")
        .then((payload) => {
            setText("pro-active-contracts-count", payload.active_contracts ?? 0);
            setText("pro-completed-jobs-count", payload.completed_jobs ?? 0);
            setText("pro-remaining-applies-count", payload.remaining_apply ?? 0);
        })
        .catch(() => {
            setText("pro-active-contracts-count", "--");
            setText("pro-completed-jobs-count", "--");
            setText("pro-remaining-applies-count", "--");
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
                Active contracts show a <strong>Complete</strong> button. Completed rows become read-only.
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
            const isActive = (contract.status || "").toLowerCase() === "active";
            const statusLabel = (contract.status || "active").toLowerCase() === "completed"
                ? "Complete"
                : contract.status || "Active";
            const actionButton = isActive
                ? `
                    <button
                        type="button"
                        class="btn btn-success w-100 professional-complete-contract-button"
                        data-contract-id="${contract.id}"
                    >
                        Complete
                    </button>
                `
                : '<span class="text-muted small">Completed</span>';

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
                            statusBadge.textContent = "Complete";
                        }

                        if (actionCell) {
                            actionCell.innerHTML = '<span class="text-muted small">Complete</span>';
                        }
                    }

                    showProfessionalFeedback("success", "Contract marked as completed.");
                    loadProfessionalStats();
                })
                .catch((error) => {
                    button.disabled = false;
                    button.textContent = "Complete";
                    showProfessionalFeedback("danger", error.message || "Unable to complete contract.");
                });
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
    const darkModeLabel = document.getElementById("professional-dark-mode-label");
    const deleteAccountButton = document.getElementById("professional-delete-account-button");
    const confirmDeleteAccountButton = document.getElementById("professional-confirm-delete-account-button");
    const portfolioImageInput = document.getElementById("professional-portfolio-image-input");
    const portfolioDescriptionInput = document.getElementById("professional-portfolio-description-input");
    const portfolioLinkedJobInput = document.getElementById("professional-portfolio-linked-job-input");
    const uploadPortfolioButton = document.getElementById("professional-upload-portfolio-btn");
    const portfolioFeedback = document.getElementById("professional-portfolio-feedback");
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

    if (uploadPortfolioButton && portfolioImageInput) {
        uploadPortfolioButton.addEventListener("click", function () {
            const file = portfolioImageInput.files && portfolioImageInput.files[0];
            const description = portfolioDescriptionInput ? portfolioDescriptionInput.value.trim() : "";
            const linkedJobIdRaw = portfolioLinkedJobInput ? portfolioLinkedJobInput.value.trim() : "";

            if (!file) {
                if (portfolioFeedback) {
                    portfolioFeedback.innerHTML = '<span class="text-danger">Please select an image first.</span>';
                } else {
                    window.alert("Please select an image first.");
                }
                return;
            }

            const formData = new FormData();
            formData.append("image", file);
            if (description) {
                formData.append("description", description);
            }
            if (linkedJobIdRaw) {
                formData.append("linked_job_id", linkedJobIdRaw);
            }

            uploadPortfolioButton.disabled = true;
            uploadPortfolioButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
            if (portfolioFeedback) {
                portfolioFeedback.innerHTML = "";
            }

            fetch("/api/pro/portfolio", {
                method: "POST",
                headers: buildHeaders(),
                body: formData,
            })
                .then(async (response) => {
                    let payload = null;
                    try {
                        payload = await response.json();
                    } catch (error) {
                        payload = null;
                    }

                    if (!response.ok) {
                        throw new Error(payload?.message || "Unable to upload portfolio item.");
                    }

                    return payload;
                })
                .then(() => {
                    if (portfolioFeedback) {
                        portfolioFeedback.innerHTML = '<span class="text-success">Portfolio item uploaded successfully.</span>';
                    }
                    portfolioImageInput.value = "";
                    if (portfolioDescriptionInput) {
                        portfolioDescriptionInput.value = "";
                    }
                    if (portfolioLinkedJobInput) {
                        portfolioLinkedJobInput.value = "";
                    }
                })
                .catch((error) => {
                    if (portfolioFeedback) {
                        portfolioFeedback.innerHTML = `<span class="text-danger">${error.message || "Unable to upload portfolio item."}</span>`;
                    } else {
                        window.alert(error.message || "Unable to upload portfolio item.");
                    }
                })
                .finally(() => {
                    uploadPortfolioButton.disabled = false;
                    uploadPortfolioButton.innerHTML = '<i class="fa-solid fa-upload me-1"></i> Upload Portfolio Item';
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
