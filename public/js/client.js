document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Client dashboard module.
    const { fetchJson, postJson, deleteJson, buildHeaders } = window.EasyHireApi;
    const {
        ETHIOPIAN_CITIES,
        SKILL_OPTIONS,
        toArray,
        extractCount,
        setText,
    } = window.EasyHireUtils;
    let requestCountdownInterval = null;

    // Client UI rendering and actions.
function setContentHeader(title, subtitle, showReloadButton) {
    setText("content-title", title);
    setText("content-subtitle", subtitle);

    const reloadButton = document.getElementById("reload-job-posts-button");

    if (reloadButton) {
        reloadButton.classList.toggle("d-none", !showReloadButton);
    }
}

function formatPrice(value) {
    if (value === undefined || value === null || value === "") {
        return "N/A";
    }

    const amount = Number(value);

    if (Number.isNaN(amount)) {
        return value;
    }

    const formattedAmount = Number.isInteger(amount)
        ? amount.toLocaleString()
        : amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return `Br ${formattedAmount}`;
}

function shortText(text) {
    if (!text) {
        return "No description provided.";
    }

    return text.length > 120 ? text.slice(0, 117) + "..." : text;
}

function formatDate(value) {
    if (!value) {
        return "N/A";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString();
}

function renderJobPosts(jobPosts) {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    setContentHeader("Job Posts", "Latest client job posts", true);

    if (!jobPosts.length) {
        contentArea.innerHTML =
            '<div class="alert alert-light border mb-0">No job posts found.</div>';
        return;
    }

    const rows = jobPosts
        .map((jobPost) => {
            const title = jobPost.title || "Untitled";
            const skill = jobPost.skill || "N/A";
            const status = jobPost.status || "N/A";
            const location =
                jobPost.location ||
                jobPost.city ||
                jobPost.address ||
                "N/A";
            const startDate = jobPost.start_date ? formatDate(jobPost.start_date) : "Not set";
            const deadline = jobPost.deadline ? formatDate(jobPost.deadline) : "Not set";
            const jobId = jobPost.id;
            const canDelete = status === 'open' || status === 'expired';

            return `
                <tr data-testid="client-job-row-${jobId}">
                    <td class="fw-semibold">${title}</td>
                    <td>${skill}</td>
                    <td><span class="badge text-bg-light border">${status}</span></td>
                    <td>${location}</td>
                    <td>${startDate}</td>
                    <td>${deadline}</td>
                    <td>
                        ${canDelete ? `<button type="button" class="btn btn-sm btn-outline-danger delete-job-btn" data-job-id="${jobId}" data-job-title="${title}" data-testid="client-delete-job-${jobId}">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>` : '<span class="text-muted small">No action</span>'}
                    </td>
                </tr>
            `;
        })
        .join("");

    contentArea.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Skill</th>
                        <th scope="col">Status</th>
                        <th scope="col">Location</th>
                        <th scope="col">Start Date</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
    
    bindDeleteJobButtons();
}

function renderJobPostsError() {
    const contentArea = document.getElementById("content-area");

    if (contentArea) {
        setContentHeader("Job Posts", "Latest client job posts", true);
        contentArea.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load job posts.</div>';
    }
}

function renderPlaceholder(title, message) {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    contentArea.innerHTML = `
        <div class="card border-0 bg-light">
            <div class="card-body py-4">
                <h3 class="h5 mb-2">${title}</h3>
                <p class="text-muted mb-0">${message}</p>
            </div>
        </div>
    `;
}

function getProfessionalPhoto(professional) {
    return (
        professional.photo ||
        professional.photo_url ||
        professional.profile_photo ||
        professional.user?.profile_photo ||
        professional.avatar ||
        professional.image ||
        "/images/user1.jpg"
    );
}

function getProfessionalSkills(professional) {
    if (typeof professional.skill === "string" && professional.skill.trim()) {
        return professional.skill;
    }

    if (Array.isArray(professional.skills)) {
        return professional.skills.join(", ");
    }

    if (typeof professional.skills === "string" && professional.skills.trim()) {
        return professional.skills;
    }

    if (Array.isArray(professional.categories)) {
        return professional.categories.join(", ");
    }

    return "Not specified";
}

function getProfessionalName(professional) {
    return (
        professional.name ||
        professional.user?.name ||
        professional.full_name ||
        [professional.first_name, professional.last_name].filter(Boolean).join(" ") ||
        "Unknown Professional"
    );
}

function getProfessionalLocation(professional) {
    return (
        professional.location ||
        professional.user?.location ||
        professional.city ||
        professional.address ||
        "N/A"
    );
}

function getProfessionalExperience(professional) {
    const experience =
        professional.experience ??
        professional.experience_years ??
        professional.years_of_experience;

    return experience !== undefined && experience !== null ? experience : "N/A";
}

function renderStars(rating) {
    const safeRating = Number(rating) || 0;
    const fullStars = Math.max(0, Math.min(5, Math.round(safeRating)));
    let stars = "";

    for (let index = 0; index < 5; index += 1) {
        stars += `<i class="fa-star ${index < fullStars ? "fas text-warning" : "far text-muted"}"></i>`;
    }

    return stars;
}

function renderProfessionalsSection() {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    setContentHeader(
        "Professionals",
        "Browse professionals by skill and location",
        false
    );

    contentArea.innerHTML = `
        <section class="professional-list-section">
            <div class="card border-0 bg-light">
                <div class="card-body p-0">
                    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #6f42c1 !important;">
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
                </div>
            </div>
        </section>
    `;

    // Load professionals from API
    function loadClientPros() {
        let skill = document.getElementById("professional-skill") ? document.getElementById("professional-skill").value : '';
        let location = document.getElementById("professional-location") ? document.getElementById("professional-location").value : '';
        
        let params = new URLSearchParams({skill: skill, location: location});
        let token = localStorage.getItem("token");
        let headers = {"Accept": "application/json"};
        if (token) headers["Authorization"] = "Bearer " + token;
        
        let resultsArea = document.getElementById("professionals-results");
        if (resultsArea) {
            resultsArea.innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';
        }
        
        console.log("Loading professionals from /api/professionals?" + params.toString());
        
        fetch("/api/professionals?" + params.toString(), {method: "GET", headers: headers})
        .then(function(r) { 
            console.log("Professionals response status:", r.status);
            if (!r.ok) throw new Error("Request failed with status " + r.status); 
            return r.json(); 
        })
        .then(function(payload) {
            console.log("Professionals payload:", payload);
            let pros = payload.data || [];
            let results = document.getElementById("professionals-results");
            if (!results) return;
            
            if (!pros.length) {
                console.log("No professionals found");
                results.innerHTML = '<div class="alert alert-light border mb-0">No professionals found.</div>';
                return;
            }
            
            console.log("Rendering " + pros.length + " professional cards");
            
            let cards = pros.map(function(p) {
                let proId = p.id;
                let name = p.name || "N/A";
                let photo = p.profile_photo ? "/storage/" + p.profile_photo : "/images/user1.jpg";
                let skillText = p.skill || "N/A";
                let locationText = p.location || "N/A";
                let rating = p.average_rating || 0;
                let reviewsCount = p.reviews_count || 0;
                let issuesRecordedCount = Number(p.issues_recorded_count ?? 0);
                let reviewText = reviewsCount > 0 ? reviewsCount + ' review' + (reviewsCount > 1 ? 's' : '') : 'No reviews';
                
                // Generate stars inline
                let starsHtml = '';
                let fullStars = Math.floor(rating);
                for (let i = 1; i <= 5; i++) {
                    starsHtml += '<i class="fa-solid fa-star" style="color: ' + (i <= fullStars ? '#ffc107;' : '#e4e5e9;') + '"></i>';
                }
                
                // Client-facing risk signal: only resolved issues (action_taken != none).
                let reportsSection = issuesRecordedCount > 0
                    ? '<div class="mt-2 pt-2 border-top small">' +
                        '<span class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i>' + issuesRecordedCount + ' issue' + (issuesRecordedCount !== 1 ? 's' : '') + ' recorded</span>' +
                        '</div>'
                    : '';
                
                return '<div class="col-md-6 col-xl-4" onclick="window.openProfessionalProfilePage(' + proId + ')" style="cursor:pointer;">' +
                    '<div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform=\'translateY(-5px)\';this.style.boxShadow=\'0 8px 25px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'\';this.style.boxShadow=\'\';">' +
                    '<div class="card-body p-4">' +
                    '<div class="d-flex align-items-center mb-3">' +
                    '<img src="' + photo + '" alt="' + name + '" class="rounded-circle object-fit-cover me-3 border" style="width: 72px; height: 72px;">' +
                    '<div><h3 class="h6 mb-1 fw-bold">' + name + '</h3>' +
                    '<div class="small">' + starsHtml + ' <span class="text-muted">(' + rating.toFixed(1) + ')</span></div></div>' +
                    '</div>' +
                    '<p class="mb-2"><span class="fw-semibold">Skills:</span> ' + skillText + '</p>' +
                    '<p class="mb-0"><span class="fw-semibold">Location:</span> ' + locationText + '</p>' +
                    '<hr>' +
                    '<div class="d-flex justify-content-between small">' +
                    '<span class="text-muted"><i class="fa-solid fa-star me-1"></i>' + reviewText + '</span>' +
                    '</div>' +
                    reportsSection +
                    '</div></div></div>';
            }).join("");
            
            results.innerHTML = '<div class="row g-4">' + cards + '</div>';
        })
        .catch(function(err) {
            console.error("Error loading professionals:", err);
            let results = document.getElementById("professionals-results");
            if (results) results.innerHTML = '<div class="alert alert-danger mb-0">Unable to load professionals. Check console for details.</div>';
        });
    }

    // Expose globally for profile modal
    window.loadClientPros = loadClientPros;

    // Bind search button
    const skillInput = document.getElementById("professional-skill");
    const locationInput = document.getElementById("professional-location");
    const triggerSearchByEnter = function (event) {
        const isEnter = event.key === "Enter" || event.code === "Enter" || event.keyCode === 13;
        if (!isEnter) return;
        event.preventDefault();
        loadClientPros();
    };

    if (skillInput) {
        skillInput.addEventListener("keydown", triggerSearchByEnter);
        skillInput.addEventListener("keypress", triggerSearchByEnter);
    }

    if (locationInput) {
        locationInput.addEventListener("keydown", triggerSearchByEnter);
        locationInput.addEventListener("keypress", triggerSearchByEnter);
    }

    let searchBtn = document.getElementById("professional-search-button");
    if (searchBtn) {
        searchBtn.addEventListener("click", loadClientPros);
        // Trigger initial load after a small delay
        setTimeout(loadClientPros, 100);
    }
}

function renderProfessionals(professionals) {
    const results = document.getElementById("professionals-results");

    if (!results) {
        return;
    }

    if (!professionals.length) {
        results.innerHTML =
            '<div class="alert alert-light border mb-0">No professionals found.</div>';
        return;
    }

    const cards = professionals
        .map((professional) => {
            const rating = professional.rating ?? professional.average_rating ?? 0;
            const profileId = professional.id ?? professional.professional_id ?? professional.profile_id ?? "";

            return `
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100 ${profileId ? "cursor-pointer" : ""}" style="border-left: 4px solid #0d6efd !important;" ${profileId ? `onclick="window.openProfessionalProfilePage('${profileId}')"` : ""}>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <img
                                    src="${getProfessionalPhoto(professional)}"
                                    alt="${getProfessionalName(professional)}"
                                    class="rounded-circle object-fit-cover me-3 border"
                                    style="width: 72px; height: 72px;"
                                >
                                <div>
                                    <h3 class="h6 mb-1 fw-bold">${getProfessionalName(professional)}</h3>
                                    <div class="text-warning small">${renderStars(rating)}</div>
                                </div>
                            </div>
                            <p class="mb-2"><span class="fw-semibold">Skills:</span> ${getProfessionalSkills(professional)}</p>
                            <p class="mb-2"><span class="fw-semibold">Location:</span> ${getProfessionalLocation(professional)}</p>
                            <p class="mb-0"><span class="fw-semibold">Experience:</span> ${getProfessionalExperience(professional)} years</p>
                        </div>
                    </div>
                </div>
            `;
        })
        .join("");

    results.innerHTML = `<div class="row g-4">${cards}</div>`;
}

function renderProfessionalsError() {
    const results = document.getElementById("professionals-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load professionals.</div>';
    }
}

function getContractTitle(contract) {
    return contract.title || contract.contract_title || "Untitled Contract";
}

function getContractClientName(contract) {
    return (
        contract.client_name ||
        contract.client?.name ||
        contract.client?.full_name ||
        "N/A"
    );
}

function openProfessionalProfilePage(profileId) {
    if (!profileId) {
        return;
    }

    const contentArea = document.getElementById("content-area");
    const isClientDashboard = window.location.pathname.includes("/client/dashboard") && !!contentArea;

    if (isClientDashboard) {
        loadProfessionalProfileInDashboard(profileId);
        return;
    }

    window.location.href = "/professional/" + encodeURIComponent(profileId);
}

function loadProfessionalProfileInDashboard(profileId) {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        window.location.href = "/professional/" + encodeURIComponent(profileId);
        return;
    }

    setContentHeader("Professional Profile", "Loading professional details...", false);
    contentArea.innerHTML = '<div class="text-muted">Loading professional profile...</div>';

    fetchJson(`/api/professionals/${profileId}`)
        .then((payload) => {
            renderProfessionalProfileInDashboard(payload);
        })
        .catch(() => {
            contentArea.innerHTML =
                '<div class="alert alert-danger mb-0">Unable to load professional profile.</div>';
        });
}

function renderProfessionalProfileInDashboard(payload) {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    const pro = payload?.professional || {};
    const reviews = Array.isArray(payload?.reviews) ? payload.reviews : [];
    const portfolioItems = Array.isArray(payload?.portfolio_items) ? payload.portfolio_items : [];
    const issuesRecordedCount = Number(payload?.issues_recorded_count ?? 0);
    const completedJobs = Array.isArray(payload?.completed_jobs) ? payload.completed_jobs : [];
    const averageRating = Number(payload?.average_rating || 0);
    const status = String(pro.status || "pending").toLowerCase();
    const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
    const statusClass = status === "approved"
        ? "bg-success-subtle text-success"
        : status === "rejected"
            ? "bg-danger-subtle text-danger"
            : "bg-warning-subtle text-warning";
    const proId = pro.id;
    const name = pro.user?.name || pro.name || "N/A";
    const photo = pro.profile_photo ? "/storage/" + pro.profile_photo : "/images/user1.jpg";
    const skills = String(pro.skill || "")
        .split(/[,/|]+/)
        .map((item) => item.trim())
        .filter(Boolean);

    const stars = renderStars(averageRating);
    const reviewsHtml = reviews.length
        ? reviews.map((review, index) => `
            <div class="list-group-item mb-2 rounded ${index >= 3 ? "d-none dashboard-review-extra" : ""}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <i class="fa-solid fa-user me-1 text-primary"></i>
                        <strong>${review.reviewer_name || "Anonymous"}</strong>
                    </div>
                    <div>${renderStars(Number(review.rating || 0))}</div>
                </div>
                <p class="mb-1 text-muted small">${review.comment || "No comment"}</p>
                <small class="text-muted">${formatDate(review.created_at)}</small>
            </div>
        `).join("") + (reviews.length > 3 ? `
            <div class="text-center mt-3">
                <button type="button" id="dashboard-show-more-reviews-btn" class="btn btn-outline-info btn-sm">
                    <i class="fa-solid fa-angles-down me-1"></i>See more reviews
                </button>
            </div>
        ` : "")
        : '<div class="alert alert-secondary mb-0">No reviews yet.</div>';

    const reportsHtml = issuesRecordedCount > 0
        ? `
            <div class="alert alert-danger mb-0 d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-triangle-exclamation me-2"></i><strong>${issuesRecordedCount}</strong> issue${issuesRecordedCount !== 1 ? "s" : ""} recorded</span>
            </div>
        `
        : "";

    const portfolioHtml = portfolioItems.length
        ? portfolioItems.map((item) => `
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border h-100 rounded-3">
                    <img src="${item.image_url || "/images/user1.jpg"}" alt="Portfolio item" class="card-img-top" style="height: 180px; object-fit: cover;">
                    <div class="card-body border-top">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h6 class="fw-bold mb-0 text-success">Project Work</h6>
                            ${item.linked_job_id ? `<span class="badge bg-light text-dark border">Job #${item.linked_job_id}</span>` : ""}
                        </div>
                        <p class="text-muted small mb-0">${(item.description || "No description").toString().slice(0, 120)}</p>
                    </div>
                </div>
            </div>
        `).join("")
        : '<div class="alert alert-secondary mb-0">No portfolio items uploaded yet.</div>';

    const completedJobsHtml = completedJobs.length
        ? `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${completedJobs.map((application) => `
                            <tr>
                                <td class="fw-semibold">${application.job?.title || "Job"}</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td class="text-muted">${formatDate(application.job?.created_at)}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `
        : '<div class="alert alert-secondary mb-0">No completed jobs yet.</div>';

    setContentHeader(name, "Professional profile", false);

    contentArea.innerHTML = `
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border-top: 5px solid #198754 !important;">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row g-4 align-items-center">
                            <div class="col-12 col-md-auto text-center">
                                <img src="${photo}" class="rounded-circle border border-3 shadow-sm" alt="${name}" style="width: 130px; height: 130px; object-fit: cover;">
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <h2 class="h3 fw-bold mb-0 text-dark">${name}</h2>
                                    <span class="badge rounded-pill ${statusClass}">${statusLabel}</span>
                                    ${issuesRecordedCount > 0 ? `<span class="badge rounded-pill text-bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${issuesRecordedCount} issue${issuesRecordedCount !== 1 ? "s" : ""} recorded</span>` : ""}
                                </div>
                                <p class="text-muted mb-2">${pro.skill || "Professional"}</p>
                                <div class="d-flex flex-wrap align-items-center gap-3 small mb-3">
                                    <span class="d-inline-flex align-items-center">
                                        ${stars}
                                        <span class="ms-2 text-muted">${averageRating.toFixed(1)} (${reviews.length} reviews)</span>
                                    </span>
                                    <span class="text-muted"><i class="fa-solid fa-location-dot text-danger me-1"></i>${pro.location || "N/A"}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-success" id="dashboard-profile-hire-btn">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-primary mb-3"><i class="fa-solid fa-user me-2"></i>About</h4>
                        <p class="text-dark bg-light rounded-3 p-3 mb-3">${pro.bio || "No bio available."}</p>
                        <div class="alert alert-success mb-0"><i class="fa-solid fa-briefcase me-2"></i><strong>${pro.experience || 0} years</strong> experience</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-tags me-2"></i>Skills</h4>
                        ${skills.length ? `<div class="d-flex flex-wrap gap-2">${skills.map((skill) => `<span class="badge rounded-pill text-bg-light border px-3 py-2">${skill}</span>`).join("")}</div>` : '<div class="alert alert-secondary mb-0">No skills listed.</div>'}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-success mb-3"><i class="fa-solid fa-layer-group me-2"></i>Portfolio</h4>
                        ${portfolioItems.length ? `<div class="row g-3">${portfolioHtml}</div>` : portfolioHtml}
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-success mb-3"><i class="fa-solid fa-check-circle me-2"></i>Completed Jobs</h4>
                        ${completedJobsHtml}
                    </div>
                </div>
            </div>

            <div class="col-12" id="dashboard-profile-reviews-section">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold text-info mb-3"><i class="fa-solid fa-star me-2"></i>Reviews (${reviews.length})</h4>
                        <div class="list-group mb-0">${reviewsHtml}</div>
                    </div>
                </div>
            </div>

            ${issuesRecordedCount > 0 ? `
                <div class="col-12" id="dashboard-profile-reports-section">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h4 class="fw-bold text-danger mb-3"><i class="fa-solid fa-shield-halved me-2"></i>System Signal</h4>
                            ${reportsHtml}
                        </div>
                    </div>
                </div>
            ` : ""}
        </div>
    `;

    const hireBtn = document.getElementById("dashboard-profile-hire-btn");
    const reviewBtn = document.getElementById("dashboard-profile-review-btn");
    const reportBtn = document.getElementById("dashboard-profile-report-btn");
    const reviewsSection = document.getElementById("dashboard-profile-reviews-section");
    const reportsSection = document.getElementById("dashboard-profile-reports-section");
    const showMoreReviewsButton = document.getElementById("dashboard-show-more-reviews-btn");

    if (hireBtn) {
        hireBtn.addEventListener("click", function () {
            window.currentProIdForRequest = proId;
            if (typeof window.showDirectRequestModal === "function") {
                window.showDirectRequestModal();
            }
        });
    }

    if (reviewBtn) {
        reviewBtn.addEventListener("click", function () {
            if (reviewsSection) {
                reviewsSection.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    }

    if (showMoreReviewsButton) {
        showMoreReviewsButton.addEventListener("click", function () {
            document.querySelectorAll(".dashboard-review-extra").forEach(function (item) {
                item.classList.remove("d-none");
            });
            showMoreReviewsButton.classList.add("d-none");
        });
    }
}

function renderPostJobSection() {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    const locationOptions = ETHIOPIAN_CITIES.map(
        (city) => `<option value="${city}">${city}</option>`
    ).join("");
    const skillOptions = SKILL_OPTIONS.map(
        (skill) => `<option value="${skill}">${skill}</option>`
    ).join("");

    setContentHeader(
        "Post Job",
        "Create a new job post for your dashboard",
        false
    );

    contentArea.innerHTML = `
        <section class="post-job-section">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="post-job-form" class="row g-3">
                        <div class="col-12">
                            <label for="job-title" class="form-label fw-semibold">Title</label>
                            <input id="job-title" name="title" type="text" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="job-description" class="form-label fw-semibold">Description</label>
                            <textarea id="job-description" name="description" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="job-budget" class="form-label fw-semibold">Budget (Optional)</label>
                            <input id="job-budget" name="budget" type="number" min="0" step="0.01" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="job-location" class="form-label fw-semibold">Location</label>
                            <select id="job-location" name="location" class="form-select" required>
                                <option value="">Select city</option>
                                ${locationOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="job-skill" class="form-label fw-semibold">Skill</label>
                            <select id="job-skill" name="skill" class="form-select" required>
                                <option value="">Select skill</option>
                                ${skillOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="job-start-date" class="form-label fw-semibold">Start Date (Optional)</label>
                            <input id="job-start-date" name="start_date" type="date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="job-deadline" class="form-label fw-semibold">Deadline (Optional)</label>
                            <input id="job-deadline" name="deadline" type="date" class="form-control">
                        </div>
                        <div class="col-12">
                            <div id="post-job-feedback"></div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" id="submit-post-job-button" class="btn btn-success rounded-pill px-4" data-testid="client-post-job-submit">
                                Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    `;

    bindPostJobForm();
}

function bindPostJobForm() {
    const form = document.getElementById("post-job-form");
    const feedback = document.getElementById("post-job-feedback");
    const submitButton = document.getElementById("submit-post-job-button");

    if (!form || !feedback || !submitButton) {
        return;
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(form);
        const payload = {
            title: (formData.get("title") || "").toString().trim(),
            description: (formData.get("description") || "").toString().trim(),
            location: (formData.get("location") || "").toString().trim(),
            skill: (formData.get("skill") || "").toString().trim(),
        };
        const budgetValue = (formData.get("budget") || "").toString().trim();
        const startDate = (formData.get("start_date") || "").toString().trim();
        const deadline = (formData.get("deadline") || "").toString().trim();

        if (budgetValue) {
            payload.budget = budgetValue;
        }
        if (startDate) {
            payload.start_date = startDate;
        }
        if (deadline) {
            payload.deadline = deadline;
        }

        submitButton.disabled = true;
        feedback.innerHTML = '<div class="text-muted">Posting job...</div>';

        postJson("/api/job-posts", payload)
            .then(() => {
                feedback.innerHTML =
                    '<div class="alert alert-success mb-0">Job posted successfully.</div>';
                loadStats();
                loadJobPosts();
            })
            .catch(() => {
                submitButton.disabled = false;
                feedback.innerHTML =
                    '<div class="alert alert-danger mb-0">Unable to post job. Check your subscription and form values.</div>';
            });
    });
}

function bindDeleteJobButtons() {
    document.querySelectorAll(".delete-job-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const jobId = this.dataset.jobId;
            const jobTitle = this.dataset.jobTitle;
            
            window.pendingDeleteJobId = jobId;
            
            document.getElementById("delete-job-title").textContent = `"${jobTitle}"`;
            document.getElementById("delete-job-refund-notice").classList.remove("d-none");
            
            bootstrap.Modal.getOrCreateInstance(document.getElementById("delete-job-modal")).show();
        });
    });
    
    const confirmDeleteBtn = document.getElementById("confirm-delete-job-btn");
    if (confirmDeleteBtn) {
        // Avoid stacking duplicate listeners on repeated renders.
        confirmDeleteBtn.onclick = function() {
            const jobId = window.pendingDeleteJobId;
            const btn = this;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            
            fetch(`/api/job-posts/${jobId}`, {
                method: "DELETE",
                headers: buildHeaders(),
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));
                    
                    if (!response.ok) {
                        throw new Error(payload?.message || "Failed to delete");
                    }
                    
                    bootstrap.Modal.getInstance(document.getElementById("delete-job-modal")).hide();
                    
                    loadJobPosts();
                    loadStats();
                })
                .catch((error) => {
                    const errorMsg = document.getElementById("content-area");
                    if (errorMsg) {
                        errorMsg.innerHTML = '<div class="alert alert-danger mb-0">' + (error.message || "Unable to delete job") + '</div>';
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-trash me-1"></i> Delete';
                });
        };
    }
}

function getContractProfessionalName(contract) {
    return (
        contract.professional_name ||
        contract.professional?.name ||
        contract.professional?.full_name ||
        "N/A"
    );
}

function getContractClientPhone(contract) {
    return (
        contract.client_phone ||
        contract.client?.phone ||
        contract.client?.phone_number ||
        "N/A"
    );
}

function getContractProfessionalPhone(contract) {
    return (
        contract.professional_phone ||
        contract.professional?.phone ||
        contract.professional?.phone_number ||
        "N/A"
    );
}

function renderContractsSection() {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    setContentHeader(
        "All Contracts",
        "Client and professional contract records",
        false
    );

    contentArea.innerHTML = `
        <section class="contracts-list-section">
            <div id="contracts-results">
                <div class="text-muted">Loading contracts...</div>
            </div>
        </section>
    `;
}

function renderContracts(contracts) {
    const results = document.getElementById("contracts-results");

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
            const status = contract.status || "N/A";
            const createdDate = formatDate(
                contract.created_at || contract.createdAt
            );

            // Check if already reviewed/reported
            const hasReview = contract.has_review || false;
            const hasReport = contract.has_report || false;
            const reportedByProfessional = Boolean(contract.reported_by_professional);
            const professionalReportStatus = String(contract.professional_report_status || "").toLowerCase();
            const professionalReportBadge = reportedByProfessional
                ? `<span class="badge ${professionalReportStatus === "resolved" ? "bg-secondary" : "bg-warning text-dark"} ms-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>${professionalReportStatus === "resolved" ? "Reported by Pro (Resolved)" : "Reported by Pro"}</span>`
                : "";
            
            // Only show completion decision buttons when waiting for client confirmation.
            let actionButtons = '';
            
            if (status === 'pending_completion') {
                actionButtons = `
                    <div class="d-flex justify-content-end gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-success contract-action-button"
                            data-action="confirm"
                            data-contract-id="${contract.id}"
                        >
                            Confirm
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-warning contract-action-button"
                            data-action="reject"
                            data-contract-id="${contract.id}"
                        >
                            Reject
                        </button>
                    </div>
                `;
            } else if (status === 'completed') {
                if (hasReview || hasReport) {
                    actionButtons = `
                        ${hasReview ? '<span class="badge bg-success me-1"><i class="fa-solid fa-star me-1"></i>Reviewed</span>' : ''}
                        ${hasReport ? '<span class="badge bg-danger"><i class="fa-solid fa-flag me-1"></i>Reported</span>' : ''}
                        ${!hasReview || !hasReport ? `
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary contract-action-button ms-1"
                                data-action="feedback"
                                data-contract-id="${contract.id}"
                            >
                                Rate/Report
                            </button>
                        ` : ''}
                    `;
                } else {
                    actionButtons = `
                        <div class="d-flex justify-content-end gap-2">
                            <span class="badge bg-success me-1"><i class="fa-solid fa-check me-1"></i>Completed</span>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary contract-action-button"
                                data-action="feedback"
                                data-contract-id="${contract.id}"
                            >
                                Rate/Report
                            </button>
                        </div>
                    `;
                }
            } else if (status === 'cancelled') {
                actionButtons = '<span class="badge bg-danger">Cancelled</span>';
            } else if (status === 'active') {
                actionButtons = `
                    <div class="d-flex justify-content-end gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger contract-action-button"
                            data-action="cancel"
                            data-contract-id="${contract.id}"
                        >
                            Cancel
                        </button>
                        ${professionalReportBadge}
                    </div>
                `;
            } else {
                actionButtons = `<span class="badge bg-secondary">Pending</span>${professionalReportBadge}`;
            }

            if ((status === 'pending_completion' || status === 'completed' || status === 'cancelled') && professionalReportBadge) {
                actionButtons += professionalReportBadge;
            }

            return `
                <tr>
                    <td class="fw-semibold">${getContractTitle(contract)}</td>
                    <td>${getContractClientName(contract)}</td>
                    <td>${getContractClientPhone(contract)}</td>
                    <td>${getContractProfessionalName(contract)}</td>
                    <td>${getContractProfessionalPhone(contract)}</td>
                    <td><span class="badge text-bg-light border">${status}</span></td>
                    <td>${createdDate}</td>
                    <td class="text-end">${actionButtons}</td>
                </tr>
            `;
        })
        .join("");

    results.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Contract Title</th>
                        <th scope="col">Client Name</th>
                        <th scope="col">Client Phone</th>
                        <th scope="col">Professional Name</th>
                        <th scope="col">Professional Phone</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    bindContractActions();
}

function renderContractsError() {
    const results = document.getElementById("contracts-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load contracts.</div>';
    }
}

function getApplicationJobTitle(application) {
    return (
        application.job_title ||
        application.job?.title ||
        application.title ||
        "Untitled Job"
    );
}

function getApplicationProfessionalName(application) {
    return (
        application.professional_name ||
        application.professional?.name ||
        application.professional?.full_name ||
        application.user?.name ||
        "N/A"
    );
}

function renderApplicationsSection() {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    setContentHeader(
        "Applications",
        "Client application records",
        false
    );

    contentArea.innerHTML = `
        <section class="applications-list-section">
            <div id="applications-results">
                <div class="text-muted">Loading applications...</div>
            </div>
        </section>
    `;
}

function renderApplications(applications) {
    const results = document.getElementById("applications-results");

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
            const status = application.status || "N/A";
            const appliedDate = formatDate(
                application.applied_at ||
                    application.created_at ||
                    application.date_applied
            );
            const profileId = application.professional_profile_id || "";
            const actionButtons =
                status === "pending"
                    ? `
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-success application-action-button"
                                data-action="accept"
                                data-application-id="${application.id}"
                                data-testid="client-application-accept-${application.id}"
                            >
                                Accept
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger application-action-button"
                                data-action="reject"
                                data-application-id="${application.id}"
                                data-testid="client-application-reject-${application.id}"
                            >
                                Reject
                            </button>
                        </div>
                    `
                    : '<span class="text-muted small">No actions</span>';
            const professionalName = profileId
                ? `<button type="button" class="btn btn-link p-0 align-baseline professional-profile-link" data-profile-id="${profileId}">${getApplicationProfessionalName(application)}</button>`
                : getApplicationProfessionalName(application);

            return `
                <tr>
                    <td class="fw-semibold">${getApplicationJobTitle(application)}</td>
                    <td>${appliedDate}</td>
                    <td><span class="badge text-bg-light border">${status}</span></td>
                    <td>${professionalName}</td>
                    <td>${actionButtons}</td>
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
                        <th scope="col">Date Applied</th>
                        <th scope="col">Status</th>
                        <th scope="col">Professional Name</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    bindApplicationActions();
    bindProfessionalProfileLinks();
}

function renderApplicationsError() {
    const results = document.getElementById("applications-results");

    if (results) {
        results.innerHTML =
            '<div class="alert alert-danger mb-0">Unable to load applications.</div>';
    }
}

function clearRequestCountdownTicker() {
    if (requestCountdownInterval) {
        clearInterval(requestCountdownInterval);
        requestCountdownInterval = null;
    }
}

function formatDuration(seconds) {
    const safeSeconds = Math.max(0, Number(seconds) || 0);
    const hrs = Math.floor(safeSeconds / 3600);
    const mins = Math.floor((safeSeconds % 3600) / 60);
    const secs = safeSeconds % 60;
    return `${String(hrs).padStart(2, "0")}:${String(mins).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
}

function tickRequestCountdowns() {
    const nowMs = Date.now();
    document.querySelectorAll("[data-request-expires-at]").forEach((el) => {
        const expiresAt = el.getAttribute("data-request-expires-at");
        const expiresMs = Date.parse(expiresAt || "");
        if (!Number.isFinite(expiresMs)) {
            el.textContent = "N/A";
            return;
        }
        const remaining = Math.floor((expiresMs - nowMs) / 1000);
        el.textContent = remaining > 0 ? formatDuration(remaining) : "Expired";
    });
}

function renderRequestsSection() {
    const contentArea = document.getElementById("content-area");
    if (!contentArea) {
        return;
    }

    setContentHeader("Requests", "Manage your direct requests", false);
    contentArea.innerHTML = `
        <section class="client-requests-section">
            <div id="client-requests-results">
                <div class="text-muted">Loading requests...</div>
            </div>
        </section>
    `;
}

function renderRequestCards(requests, type) {
    if (!requests || !requests.length) {
        return '<div class="alert alert-light border mb-0">No requests.</div>';
    }

    return requests.map((req) => {
        const professionalName = req.professional?.name || "Unknown";
        const budget = req.budget ? `<span class="badge bg-success">Br${req.budget}</span>` : "";
        const createdAt = formatDate(req.created_at);
        const pendingCountdown = type === "pending"
            ? `<div class="small text-warning"><i class="fa-regular fa-clock me-1"></i>Expires in <strong data-request-expires-at="${req.expires_at || ""}">${formatDuration(req.seconds_remaining || 0)}</strong></div>`
            : "";

        const expiredBadge = type === "expired"
            ? '<span class="badge bg-secondary">Expired</span>'
            : "";

        const actions = type === "pending"
            ? `<button type="button" class="btn btn-sm btn-outline-danger client-request-cancel-btn" data-request-id="${req.id}">Cancel</button>`
            : type === "expired"
                ? `<button type="button" class="btn btn-sm btn-outline-primary client-request-resend-btn" data-request-id="${req.id}">Resend</button>`
                : '<span class="text-muted small">No action</span>';

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">${req.title || "Untitled"}</h6>
                        ${expiredBadge}
                    </div>
                    <p class="text-muted mb-2">${req.description || "No description"}</p>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="small text-muted">Professional: <span class="fw-semibold text-dark">${professionalName}</span></div>
                            <div class="small text-muted">Created: ${createdAt}</div>
                            ${pendingCountdown}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${budget}
                            ${actions}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join("");
}

function bindClientRequestActions() {
    document.querySelectorAll(".client-request-cancel-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const requestId = button.dataset.requestId;
            if (!requestId) {
                return;
            }
            button.disabled = true;
            button.textContent = "Cancelling...";
            postJson(`/api/client/requests/${requestId}/cancel`)
                .then(() => loadClientRequests())
                .catch((error) => {
                    button.disabled = false;
                    button.textContent = "Cancel";
                    window.alert(error.message || "Unable to cancel request.");
                });
        });
    });

    document.querySelectorAll(".client-request-resend-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const requestId = button.dataset.requestId;
            if (!requestId) {
                return;
            }
            button.disabled = true;
            button.textContent = "Resending...";
            postJson(`/api/client/requests/${requestId}/resend`)
                .then(() => loadClientRequests())
                .catch((error) => {
                    button.disabled = false;
                    button.textContent = "Resend";
                    window.alert(error.message || "Unable to resend request.");
                });
        });
    });
}

function renderClientRequests(payload) {
    const area = document.getElementById("client-requests-results");
    if (!area) {
        return;
    }

    clearRequestCountdownTicker();

    const grouped = payload?.data || {};
    const pending = grouped.pending || [];
    const accepted = grouped.accepted || [];
    const rejected = grouped.rejected || [];
    const expired = grouped.expired || [];

    area.innerHTML = `
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning-subtle fw-bold">Pending (${pending.length})</div>
                    <div class="card-body">${renderRequestCards(pending, "pending")}</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success-subtle fw-bold">Accepted (${accepted.length})</div>
                    <div class="card-body">${renderRequestCards(accepted, "accepted")}</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger-subtle fw-bold">Rejected (${rejected.length})</div>
                    <div class="card-body">${renderRequestCards(rejected, "rejected")}</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-secondary-subtle fw-bold">Expired (${expired.length})</div>
                    <div class="card-body">${renderRequestCards(expired, "expired")}</div>
                </div>
            </div>
        </div>
    `;

    bindClientRequestActions();
    tickRequestCountdowns();
    if (pending.length > 0) {
        requestCountdownInterval = setInterval(tickRequestCountdowns, 1000);
    }
}

function renderClientRequestsError() {
    const area = document.getElementById("client-requests-results");
    if (area) {
        area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load requests.</div>';
    }
}

function showApplicationErrorModal(message) {
    const modalElement = document.getElementById("application-error-modal");
    const messageElement = document.getElementById("application-error-message");

    if (!modalElement || !messageElement) {
        window.alert(message);
        return;
    }

    messageElement.textContent = message;
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function getSubscriptionPlanId(subscription) {
    return subscription.plan_id || subscription.id || subscription.plan?.id || "";
}

function getSubscriptionPlanName(subscription) {
    return (
        subscription.plan_name ||
        subscription.plan ||
        subscription.name ||
        subscription.plan?.name ||
        "No Active Plan"
    );
}

function getSubscriptionPrice(subscription) {
    return (
        subscription.price ||
        subscription.plan_price ||
        subscription.plan?.price ||
        "N/A"
    );
}

function getSubscriptionJobPostLimit(subscription) {
    return (
        subscription.job_post_limit ||
        subscription.post_limit ||
        subscription.job_limit ||
        subscription.plan?.job_posts_limit ||
        "N/A"
    );
}

function getSubscriptionDuration(subscription) {
    return (
        subscription.duration_days ||
        subscription.plan?.duration_days ||
        subscription.duration ||
        "N/A"
    );
}

function getSubscriptionExpiry(subscription) {
    return (
        subscription.expires_at ||
        subscription.expiresAt ||
        subscription.end_date ||
        "N/A"
    );
}

function renderSubscription(subscription) {
    setText("subscription-plan-name", getSubscriptionPlanName(subscription));
    setText(
        "subscription-plan-price",
        formatPrice(getSubscriptionPrice(subscription))
    );
    setText(
        "subscription-job-post-limit",
        getSubscriptionJobPostLimit(subscription)
    );

    const currentPlanName = document.getElementById("current-plan-name");
    const currentPlanDuration = document.getElementById("current-plan-duration");
    const currentPlanExpiry = document.getElementById("current-plan-expiry");
    const currentPlanJobs = document.getElementById("current-plan-jobs");

    if (currentPlanName) currentPlanName.textContent = getSubscriptionPlanName(subscription);
    if (currentPlanDuration) currentPlanDuration.textContent = getSubscriptionDuration(subscription) + " days";
    
    if (currentPlanExpiry) {
        const expiry = getSubscriptionExpiry(subscription);
        if (expiry && expiry !== "N/A") {
            currentPlanExpiry.textContent = new Date(expiry).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        } else {
            currentPlanExpiry.textContent = "--";
        }
    }

    if (currentPlanJobs) currentPlanJobs.textContent = getSubscriptionJobPostLimit(subscription);

    const currentPlanRequests = document.getElementById("current-plan-requests");
    if (currentPlanRequests) {
        const requests = subscription?.direct_requests_remaining ?? 0;
        currentPlanRequests.textContent = requests;
    }

    const actionButton = document.getElementById("subscription-action-button");

    if (actionButton) {
        actionButton.dataset.planId = getSubscriptionPlanId(subscription);
    }
}

function renderSubscriptionError() {
    setText("subscription-plan-name", "Plan unavailable");
    setText("subscription-plan-price", "N/A");
    setText("subscription-job-post-limit", "N/A");

    const currentPlanName = document.getElementById("current-plan-name");
    const currentPlanDuration = document.getElementById("current-plan-duration");
    const currentPlanExpiry = document.getElementById("current-plan-expiry");
    const currentPlanJobs = document.getElementById("current-plan-jobs");

    if (currentPlanName) currentPlanName.textContent = "No Active Plan";
    if (currentPlanDuration) currentPlanDuration.textContent = "--";
    if (currentPlanExpiry) currentPlanExpiry.textContent = "--";
    if (currentPlanJobs) currentPlanJobs.textContent = "--";
}

function handleSubscriptionAction() {
    const actionButton = document.getElementById("subscription-action-button");

    if (!actionButton) {
        return;
    }

    actionButton.addEventListener("click", function () {
        const planId = actionButton.dataset.planId;

        if (!planId) {
            return;
        }

        buyPlan(planId);
    });
}

function buyPlan(planId) {
    const token = localStorage.getItem('token');
    if (!token) {
        alert('Please login to buy a plan');
        window.location.href = '/login';
        return;
    }

    fetch(`/api/buy-plan/${planId}`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    })
    .then(async (res) => {
        let data = {};
        try {
            data = await res.json();
        } catch (error) {
            data = {};
        }

        if (!res.ok) {
            throw new Error(data.message || 'Failed to purchase plan.');
        }

        return data;
    })
    .then((data) => {
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
            return;
        }

        alert(data.message || 'Plan purchase initialized.');
    })
    .catch((err) => {
        alert(err.message || 'Failed to purchase plan. Please try again.');
    });
}

function loadSubscription() {
    return fetchJson("/api/client/my-subscription")
        .then((payload) => {
            if (payload.has_subscription === false) {
                renderSubscriptionError();
                return;
            }
            
            const subscription = Array.isArray(payload?.data)
                ? payload.data[0] || {}
                : payload?.data || payload;

            renderSubscription(subscription || {});
        })
        .catch(() => {
            renderSubscriptionError();
        });
}

function loadApplicationsResults() {
    fetchJson("/api/client/applications")
        .then((payload) => {
            renderApplications(toArray(payload));
        })
        .catch(() => {
            renderApplicationsError();
        });
}

function loadContractsResults() {
    fetchJson("/api/client/contracts")
        .then((payload) => {
            renderContracts(toArray(payload));
        })
        .catch(() => {
            renderContractsError();
        });
}

function bindProfessionalSearch() {
    const skillInput = document.getElementById("professional-skill");
    const locationInput = document.getElementById("professional-location");
    const searchButton = document.getElementById("professional-search-button");

    if (!skillInput || !locationInput || !searchButton) {
        return;
    }

    const triggerSearch = () => {
        loadProfessionalsResults(skillInput.value, locationInput.value);
    };

    const handleEnter = (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            triggerSearch();
        }
    };

    skillInput.addEventListener("keydown", handleEnter);
    locationInput.addEventListener("keydown", handleEnter);
    skillInput.addEventListener("input", triggerSearch);
    locationInput.addEventListener("input", triggerSearch);
    searchButton.addEventListener("click", triggerSearch);
}

function loadProfessionalsResults(skill = "", location = "") {
    const params = new URLSearchParams({
        skill,
        location,
    });

    fetchJson(`/api/professionals?${params.toString()}`)
        .then((payload) => {
            renderProfessionals(toArray(payload));
        })
        .catch(() => {
            renderProfessionalsError();
        });
}

function bindProfessionalProfileLinks() {
    document.querySelectorAll(".professional-profile-link").forEach((link) => {
        link.addEventListener("click", function () {
            const profileId = link.dataset.profileId;

            if (profileId) {
                openProfessionalProfilePage(profileId);
            }
        });
    });
}

function bindApplicationActions() {
    document.querySelectorAll(".application-action-button").forEach((button) => {
        button.addEventListener("click", function () {
            const action = button.dataset.action;
            const applicationId = button.dataset.applicationId;

            if (!action || !applicationId) {
                return;
            }

            button.disabled = true;

            postJson(`/api/applications/${applicationId}/${action}`)
                .then(() => {
                    loadApplicationsResults();
                    loadStats();
                    if (typeof loadProfessionalStats === 'function') loadProfessionalStats();
                })
                .catch((error) => {
                    button.disabled = false;
                    const message = error.message || `Unable to ${action} application.`;
                    showApplicationErrorModal(message);
                    
                    // Refresh applications list after modal closes
                    document.getElementById('application-error-modal').addEventListener('hidden.bs.modal', function handler() {
                        loadApplicationsResults();
                        loadStats();
                        if (typeof loadProfessionalStats === 'function') loadProfessionalStats();
                        this.removeEventListener('hidden.bs.modal', handler);
                    });
                });
        });
    });
}

function bindContractActions() {
    document.querySelectorAll(".contract-action-button").forEach((button) => {
        button.addEventListener("click", function () {
            const action = button.dataset.action;
            const contractId = button.dataset.contractId;

            if (!action || !contractId) {
                return;
            }

            if (action === "confirm") {
                openContractConfirmModal(contractId, "confirm");
                return;
            }

            if (action === "feedback") {
                openContractConfirmModal(contractId, "feedback");
                return;
            }

            button.disabled = true;

            postJson(`/api/contracts/${contractId}/${action}`)
                .then(() => {
                    loadAllContracts();
                    loadStats();
                    if (typeof loadProfessionalStats === 'function') loadProfessionalStats();
                })
                .catch(() => {
                    button.disabled = false;
                    window.alert(`Unable to ${action} contract.`);
                });
        });
    });
}

function ensureContractConfirmModal() {
    let modalElement = document.getElementById("contract-confirm-modal");

    if (modalElement) {
        return modalElement;
    }

    modalElement = document.createElement("div");
    modalElement.innerHTML = `
        <div class="modal fade" id="contract-confirm-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contract-confirm-title">Confirm Contract</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="contract-confirm-form">
                        <div class="modal-body">
                            <input type="hidden" id="contract-confirm-id">
                            <input type="hidden" id="contract-confirm-mode" value="confirm">
                            <div class="mb-3">
                                <label for="contract-rating" class="form-label fw-semibold">Rating <span class="text-muted">(Optional)</span></label>
                                <select id="contract-rating" class="form-select">
                                    <option value="">Select rating</option>
                                    <option value="5">5</option>
                                    <option value="4">4</option>
                                    <option value="3">3</option>
                                    <option value="2">2</option>
                                    <option value="1">1</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="contract-review-comment" class="form-label fw-semibold">Review</label>
                                <textarea id="contract-review-comment" class="form-control" rows="4" placeholder="Write review"></textarea>
                            </div>
                            <div class="mb-0">
                                <label for="contract-report-reason" class="form-label fw-semibold">Report</label>
                                <textarea id="contract-report-reason" class="form-control" rows="3" placeholder="Optional report reason"></textarea>
                            </div>
                            <div id="contract-confirm-feedback" class="mt-3"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="contract-confirm-submit" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modalElement.firstElementChild);
    bindContractConfirmForm();

    return document.getElementById("contract-confirm-modal");
}

function openContractConfirmModal(contractId, mode = "confirm") {
    const modalElement = ensureContractConfirmModal();
    const contractIdInput = document.getElementById("contract-confirm-id");
    const modeInput = document.getElementById("contract-confirm-mode");
    const titleElement = document.getElementById("contract-confirm-title");
    const ratingInput = document.getElementById("contract-rating");
    const reviewInput = document.getElementById("contract-review-comment");
    const reportInput = document.getElementById("contract-report-reason");
    const feedback = document.getElementById("contract-confirm-feedback");
    const submitButton = document.getElementById("contract-confirm-submit");

    if (!modalElement || !contractIdInput || !modeInput || !titleElement || !ratingInput || !reviewInput || !reportInput || !feedback) {
        return;
    }

    contractIdInput.value = contractId;
    modeInput.value = mode;
    titleElement.textContent = mode === "feedback" ? "Rate / Review / Report" : "Confirm Contract";
    ratingInput.value = "";
    reviewInput.value = "";
    reportInput.value = "";
    feedback.innerHTML = "";
    
    if (submitButton) {
        submitButton.disabled = false;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
}

function bindContractConfirmForm() {
    const form = document.getElementById("contract-confirm-form");

    if (!form) {
        return;
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const contractId = document.getElementById("contract-confirm-id")?.value;
        const mode = document.getElementById("contract-confirm-mode")?.value || "confirm";
        const rating = document.getElementById("contract-rating")?.value;
        const comment = document.getElementById("contract-review-comment")?.value || "";
        const reportReason = document.getElementById("contract-report-reason")?.value || "";
        const feedback = document.getElementById("contract-confirm-feedback");
        const submitButton = document.getElementById("contract-confirm-submit");
        const modalElement = document.getElementById("contract-confirm-modal");

        if (!contractId || !feedback || !submitButton || !modalElement) {
            return;
        }

        const shouldSendReview = Boolean(rating);
        const shouldSendReport = Boolean(reportReason.trim());

        if (!shouldSendReview && !shouldSendReport && mode === "feedback") {
            feedback.innerHTML =
                '<div class="alert alert-warning mb-0">Add a rating or report reason before submitting.</div>';
            return;
        }

        submitButton.disabled = true;
        feedback.innerHTML = mode === "feedback"
            ? '<div class="text-muted">Submitting feedback...</div>'
            : '<div class="text-muted">Submitting confirmation...</div>';

        const confirmPromise = mode === "confirm"
            ? postJson(`/api/contracts/${contractId}/confirm`)
            : Promise.resolve(null);

        confirmPromise
            .then(() => {
                if (!shouldSendReview) {
                    return { reviewOk: true, reviewSkipped: true };
                }

                return postJson(`/api/contracts/${contractId}/review`, {
                    rating: Number(rating),
                    comment,
                }).then(() => ({ reviewOk: true }))
                    .catch((error) => ({ reviewOk: false, reviewError: error?.message || "Unable to submit review." }));
            })
            .then((reviewResult) => {
                if (!shouldSendReport) {
                    return { ...reviewResult, reportOk: true, reportSkipped: true };
                }

                return postJson(`/api/contracts/${contractId}/report`, {
                    reason: reportReason.trim(),
                })
                    .then(() => ({ ...reviewResult, reportOk: true }))
                    .catch((error) => ({ ...reviewResult, reportOk: false, reportError: error?.message || "Unable to submit report." }));
            })
            .then((result) => {
                const reviewOk = result?.reviewOk !== false;
                const reportOk = result?.reportOk !== false;
                const messages = [];

                if (mode === "confirm") {
                    messages.push("Contract confirmed successfully.");
                }

                if (shouldSendReview) {
                    messages.push(reviewOk ? "Review submitted." : `Review failed: ${result.reviewError || "Unable to submit review."}`);
                }

                if (shouldSendReport) {
                    messages.push(reportOk ? "Report submitted." : `Report failed: ${result.reportError || "Unable to submit report."}`);
                }

                const hasFailure = (shouldSendReview && !reviewOk) || (shouldSendReport && !reportOk);
                const alertType = hasFailure ? "warning" : "success";

                feedback.innerHTML = `<div class="alert alert-${alertType} mb-0">${messages.join("<br>")}</div>`;

                loadAllContracts();
                loadStats();

                if (!hasFailure) {
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modal.hide();
                    }, 800);
                } else {
                    submitButton.disabled = false;
                }
            })
            .catch((error) => {
                submitButton.disabled = false;
                feedback.innerHTML =
                    `<div class="alert alert-danger mb-0">${error?.message || "Unable to submit request."}</div>`;
            });
    });
}

function showProProfile(proId) {
    openProfessionalProfilePage(proId);
}

function setActiveSidebarButton(view) {
    clearRequestCountdownTicker();
    document.querySelectorAll(".sidebar-nav-button").forEach((button) => {
        const isActive = button.dataset.view === view;

        button.classList.toggle("btn-dark", isActive);
        button.classList.toggle("text-white", isActive);
        button.classList.toggle("btn-outline-dark", !isActive);
    });
}

function bindSidebarNavigation() {
    document.querySelectorAll(".sidebar-nav-button").forEach((button) => {
        button.addEventListener("click", function () {
            const view = button.dataset.view;

            if (view === "job-posts") {
                loadJobPosts();
                return;
            }

            if (view === "post-job") {
                loadPostJob();
                return;
            }

            if (view === "professionals") {
                loadProfessionals();
                return;
            }

            if (view === "applications") {
                loadApplications();
                return;
            }

            if (view === "contracts") {
                loadAllContracts();
                return;
            }

            if (view === "requests") {
                loadClientRequests();
                return;
            }
        });
    });
}

function bindJobPostsReload() {
    const reloadButton = document.getElementById("reload-job-posts-button");

    if (!reloadButton) {
        return;
    }

    reloadButton.addEventListener("click", function () {
        loadJobPosts();
    });
}

function loadClientProfileForSettings() {
    const token = localStorage.getItem("token");
    
    fetch("/api/client/profile", {
        headers: {
            "Accept": "application/json",
            "Authorization": "Bearer " + token,
        },
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success && data.data) {
                const d = data.data;
                
                const nameInput = document.getElementById("profile-name");
                const emailInput = document.getElementById("profile-email");
                const phoneInput = document.getElementById("profile-phone");
                const photoPreview = document.getElementById("settings-profile-preview");
                
                if (nameInput) nameInput.value = d.name || "";
                if (emailInput) emailInput.value = d.email || "";
                if (phoneInput) phoneInput.value = d.phone || "";
                if (photoPreview) {
                    photoPreview.src = d.profile_photo || "/images/user1.jpg";
                }
            }
        })
        .catch((err) => {
            console.error("Error loading profile:", err);
        });
}

function bindClientProfileForm() {
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
    
    const saveBtn = document.getElementById("save-profile-btn");
    if (!saveBtn) return;

    const photoInput = document.getElementById("settings-photo-input");
    if (photoInput) {
        photoInput.addEventListener("change", function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById("settings-profile-preview");
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    saveBtn.addEventListener("click", function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append("name", document.getElementById("profile-name").value);
        formData.append("email", document.getElementById("profile-email").value);
        formData.append("phone", document.getElementById("profile-phone").value);

        const photoInputForSave = document.getElementById("settings-photo-input");
        if (photoInputForSave && photoInputForSave.files.length > 0) {
            formData.append("profile_photo", photoInputForSave.files[0]);
        }

        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        fetch("/api/client/profile", {
            method: "POST",
            headers: buildHeaders(),
            body: formData,
        })
        .then((res) => res.json())
        .then((data) => {
            console.log("Profile update response:", data);
            if (data.success) {
                alert("Profile updated successfully!");
                
                // Update sidebar and topbar photos if photo was changed
                if (photoInputForSave && photoInputForSave.files.length > 0 && data.data && data.data.profile_photo) {
                    const timestamp = new Date().getTime();
                    const newPhoto = data.data.profile_photo + '?t=' + timestamp;
                    
                    const sidebarPhoto = document.getElementById("client-sidebar-photo");
                    if (sidebarPhoto) {
                        sidebarPhoto.src = newPhoto;
                    }
                    
                    const topbarPhoto = document.getElementById("client-topbar-photo");
                    if (topbarPhoto) {
                        topbarPhoto.src = newPhoto;
                    }
                }
                
                loadClientIdentity();
                loadClientProfileForSettings();
            } else {
                alert(data.message || "Failed to update profile");
            }
        })
        .catch((err) => {
            console.error("Profile update error:", err);
            alert("Failed to update profile. Please try again.");
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    });
}

function bindDashboardTools() {
    const darkModeToggle = document.getElementById("dark-mode-toggle");
    const topbarDarkModeToggle = document.getElementById("client-topbar-dark-mode");
    const settingsButton = document.getElementById("settings-button");
    const settingsModalElement = document.getElementById("dashboard-settings-modal");
    const deleteAccountModalElement = document.getElementById("delete-account-modal");
    const deleteAccountButton = document.getElementById("delete-account-button");
    const confirmDeleteAccountButton = document.getElementById("confirm-delete-account-button");
    const darkModeLabel = document.getElementById("dark-mode-label");
    const settingsModal = settingsModalElement
        ? bootstrap.Modal.getOrCreateInstance(settingsModalElement)
        : null;
    const deleteAccountModal = deleteAccountModalElement
        ? bootstrap.Modal.getOrCreateInstance(deleteAccountModalElement)
        : null;

    function syncDarkModeLabel() {
        if (!darkModeLabel) {
            return;
        }

        darkModeLabel.textContent = document.body.classList.contains("client-dashboard-dark")
            ? "Disable"
            : "Enable";
    }

    function toggleDarkMode() {
        document.body.classList.toggle("client-dashboard-dark");

        if (document.body.classList.contains("client-dashboard-dark")) {
            localStorage.setItem("client_dashboard_theme", "dark");
        } else {
            localStorage.removeItem("client_dashboard_theme");
        }

        syncDarkModeLabel();
    }

    if (localStorage.getItem("client_dashboard_theme") === "dark") {
        document.body.classList.add("client-dashboard-dark");
    }

    syncDarkModeLabel();

    if (darkModeToggle) {
        darkModeToggle.addEventListener("click", toggleDarkMode);
    }

    if (topbarDarkModeToggle) {
        topbarDarkModeToggle.addEventListener("click", toggleDarkMode);
    }

    if (settingsButton) {
        settingsButton.addEventListener("click", function () {
            if (settingsModal) {
                settingsModal.show();
            }
        });
    }
    
    // Load profile when modal is shown
    if (settingsModalElement) {
        settingsModalElement.addEventListener("shown.bs.modal", function() {
            loadClientProfileForSettings();
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
                    localStorage.removeItem("client_dashboard_theme");
                    window.location.href = "/";
                })
                .catch((error) => {
                    window.alert(error.message || "Failed to delete account.");
                })
                .finally(() => {
                    confirmDeleteAccountButton.disabled = false;
                    confirmDeleteAccountButton.textContent = "Yes";
                });
        });
    }
}

function loadClientIdentity() {
    const clientNameElement = document.getElementById("client-name");
    const sidebarPhotoElement = document.getElementById("client-sidebar-photo");
    const topbarPhotoElement = document.getElementById("client-topbar-photo");

    if (!clientNameElement && !sidebarPhotoElement && !topbarPhotoElement) {
        return Promise.resolve();
    }

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

    return fetchJson("/api/client/me")
        .then((client) => {
            if (clientNameElement) {
                clientNameElement.textContent = client.name || "Loading...";
            }

            showProfileImageWhenReady(sidebarPhotoElement, client.profile_photo);
            showProfileImageWhenReady(topbarPhotoElement, client.profile_photo);
        })
        .catch(() => {
            if (clientNameElement) {
                clientNameElement.textContent = "Unavailable";
            }

            showProfileImageWhenReady(sidebarPhotoElement, null);
            showProfileImageWhenReady(topbarPhotoElement, null);
        });
}

function setClientDashboardLoading(isLoading) {
    const loader = document.getElementById("client-dashboard-loader");

    if (loader) {
        loader.classList.toggle("d-none", !isLoading);
    }
}

function loadStats() {
    return Promise.all([
        fetchJson("/api/client/contracts/active"),
        fetchJson("/api/client/job-posts/count"),
        fetchJson("/api/client/job-posts/remaining"),
    ])
        .then(([activeContracts, jobPostsCount, remainingJobPosts]) => {
            setText("active-contracts-count", extractCount(activeContracts));
            setText("total-job-posts-count", extractCount(jobPostsCount));
            setText(
                "remaining-job-posts-count",
                extractCount(remainingJobPosts)
            );
        })
        .catch(() => {
            setText("active-contracts-count", "--");
            setText("total-job-posts-count", "--");
            setText("remaining-job-posts-count", "--");
        });
}

function loadJobPosts() {
    setActiveSidebarButton("job-posts");
    const contentArea = document.getElementById("content-area");

    if (contentArea) {
        setContentHeader("Job Posts", "Latest client job posts", true);
        contentArea.innerHTML = '<div class="text-muted">Loading job posts...</div>';
    }

    return fetchJson("/api/client/job-posts")
        .then((payload) => {
            renderJobPosts(toArray(payload));
        })
        .catch(() => {
            renderJobPostsError();
        });
}

function loadPostJob() {
    setActiveSidebarButton("post-job");
    renderPostJobSection();
}

function loadProfessionals() {
    setActiveSidebarButton("professionals");
    renderProfessionalsSection();
}

function loadApplications() {
    setActiveSidebarButton("applications");
    renderApplicationsSection();
    loadApplicationsResults();
}

function loadAllContracts() {
    setActiveSidebarButton("contracts");
    renderContractsSection();
    loadContractsResults();
}

function loadClientRequests() {
    setActiveSidebarButton("requests");
    renderRequestsSection();
    fetchJson("/api/client/requests")
        .then((payload) => {
            renderClientRequests(payload);
        })
        .catch(() => {
            renderClientRequestsError();
        });
}

function loadClientInitialView() {
    const view = new URLSearchParams(window.location.search).get("view");

    if (view === "post-job") {
        return loadPostJob();
    }

    if (view === "professionals") {
        return loadProfessionals();
    }

    if (view === "applications") {
        return loadApplications();
    }

    if (view === "contracts") {
        return loadAllContracts();
    }

    if (view === "requests") {
        return loadClientRequests();
    }

    return loadJobPosts();
}

function renderClientMessagesSection(payload) {
    const contentArea = document.getElementById("content-area");

    if (!contentArea) {
        return;
    }

    setContentHeader("Messages", "Messages from admin", true);
    const data = toArray(payload?.data);

    if (!data.length) {
        contentArea.innerHTML = '<div class="alert alert-light border mb-0">No messages yet.</div>';
        return;
    }

    const rows = data.map((item) => {
        const unread = !item.read_at;
        const subject = item.subject || "Message from Admin";
        const messageText = item.message || "";
        const sentAt = item.created_at ? new Date(item.created_at).toLocaleString() : "N/A";
        const button = unread
            ? `<button type="button" class="btn btn-sm btn-outline-primary client-message-read-btn" data-message-id="${item.id}">Mark as read</button>`
            : '<span class="badge text-bg-light border">Read</span>';

        return `
            <tr class="${unread ? "table-light" : ""}">
                <td class="fw-semibold">${subject}</td>
                <td>${messageText}</td>
                <td>${sentAt}</td>
                <td>${button}</td>
            </tr>
        `;
    }).join("");

    contentArea.innerHTML = `
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Subject</th>
                        <th scope="col">Message</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;

    document.querySelectorAll(".client-message-read-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const id = button.dataset.messageId;
            if (!id) {
                return;
            }

            postJson(`/api/messages/${id}/read`, {}).finally(() => {
                loadClientMessages();
                loadClientNotifications();
            });
        });
    });
}

function loadClientMessages() {
    setActiveSidebarButton("notifications");
    const contentArea = document.getElementById("content-area");
    if (contentArea) {
        setContentHeader("Messages", "Messages from admin", true);
        contentArea.innerHTML = '<div class="text-muted">Loading messages...</div>';
    }

    return fetchJson("/api/messages")
        .then((payload) => {
            renderClientMessagesSection(payload);
        })
        .catch(() => {
            if (contentArea) {
                contentArea.innerHTML = '<div class="alert alert-danger mb-0">Unable to load messages.</div>';
            }
        });
}

function getClientNotificationTone(type) {
    const value = String(type || "").toLowerCase();

    if (value.includes("accepted") || value.includes("confirmed") || value.includes("purchase_success")) {
        return { accent: "#198754", badgeClass: "text-bg-success", label: "Success" };
    }

    if (value.includes("rejected")) {
        return { accent: "#dc3545", badgeClass: "text-bg-danger", label: "Rejected" };
    }

    if (value.includes("received") || value.includes("requested")) {
        return { accent: "#fd7e14", badgeClass: "text-bg-warning text-dark", label: "Pending" };
    }

    if (value === "admin_message") {
        return { accent: "#0d6efd", badgeClass: "text-bg-primary", label: "Admin" };
    }

    return { accent: "#6c757d", badgeClass: "text-bg-secondary", label: "Update" };
}

function getClientNotificationDefaultActionUrl(type) {
    const value = String(type || "").toLowerCase();

    if (value.includes("application")) {
        return "/client/dashboard?view=applications";
    }

    if (value.includes("direct_request")) {
        return "/client/dashboard?view=requests";
    }

    if (value.includes("completion")) {
        return "/client/dashboard?view=contracts";
    }

    return "/client/dashboard";
}

function renderClientNotifications(payload) {
    const list = document.getElementById("client-notification-list");
    const badge = document.getElementById("client-notification-badge");

    if (!list || !badge) {
        return;
    }

    const notifications = toArray(payload?.data);
    const unreadCount = Number(payload?.unread_count ?? 0);

    if (unreadCount > 0) {
        badge.textContent = unreadCount > 99 ? "99+" : String(unreadCount);
        badge.classList.remove("d-none");
    } else {
        badge.classList.add("d-none");
    }

    if (!notifications.length) {
        list.innerHTML = '<div class="text-muted small p-3">No notifications yet.</div>';
        return;
    }

    list.innerHTML = notifications.map((item) => {
        const isUnread = !item.read_at;
        const actionUrl = item.action_url || getClientNotificationDefaultActionUrl(item.type);
        const createdAt = item.created_at ? new Date(item.created_at).toLocaleString() : "";
        const tone = getClientNotificationTone(item.type);
        const bgStyle = isUnread ? "background: rgba(13, 110, 253, 0.05);" : "";
        return `
            <button type="button"
                class="w-100 text-start border-0 px-3 py-2 client-notification-item"
                data-notification-id="${item.id}"
                data-action-url="${actionUrl}"
                style="border-left: 4px solid ${tone.accent}; ${bgStyle}">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="fw-semibold small">${item.title || "Notification"}</div>
                    <span class="badge ${tone.badgeClass}">${tone.label}</span>
                </div>
                <div class="small text-muted">${item.message || ""}</div>
                <div class="small text-muted mt-1">${createdAt}</div>
            </button>
        `;
    }).join("");

    list.querySelectorAll(".client-notification-item").forEach((button) => {
        button.addEventListener("click", function () {
            const id = button.dataset.notificationId;
            const actionUrl = button.dataset.actionUrl || "";
            if (!id) return;

            postJson(`/api/notifications/${id}/read`, {})
                .finally(() => {
                    if (actionUrl) {
                        window.location.href = actionUrl;
                        return;
                    }

                    loadClientNotifications();
                });
        });
    });
}

function loadClientNotifications() {
    return fetchJson("/api/notifications")
        .then((payload) => {
            renderClientNotifications(payload);
        })
        .catch(() => {
            const list = document.getElementById("client-notification-list");
            if (list) {
                list.innerHTML = '<div class="text-muted small p-3">Unable to load notifications.</div>';
            }
        });
}

function bindClientNotifications() {
    const markAllButton = document.getElementById("client-notification-mark-all");
    const clearButton = document.getElementById("client-notification-clear");
    const bellButton = document.getElementById("client-notification-bell");

    if (markAllButton) {
        markAllButton.addEventListener("click", function (event) {
            event.preventDefault();
            postJson("/api/notifications/read-all", {})
                .finally(() => loadClientNotifications());
        });
    }

    if (clearButton) {
        clearButton.addEventListener("click", function (event) {
            event.preventDefault();
            postJson("/api/notifications/clear", {})
                .finally(() => loadClientNotifications());
        });
    }

    if (bellButton) {
        bellButton.addEventListener("click", function () {
            loadClientNotifications();
        });
    }
}

    // Client dashboard entrypoint (called after DOM is ready).
    function init() {
        setClientDashboardLoading(true);
        bindSidebarNavigation();
        bindJobPostsReload();
        bindDashboardTools();
        bindClientNotifications();
        bindClientProfileForm();
        handleSubscriptionAction();

        Promise.allSettled([
            loadStats(),
            loadClientInitialView(),
            loadSubscription(),
            loadClientNotifications(),
            loadClientIdentity(),
        ]).finally(() => {
            setClientDashboardLoading(false);
        });
    }

    window.EasyHireClient = { init };

    // Required globals for existing inline onclick handlers and links.
    window.loadJobPosts = loadJobPosts;
    window.loadPostJob = loadPostJob;
    window.loadProfessionals = loadProfessionals;
    window.loadApplications = loadApplications;
    window.loadAllContracts = loadAllContracts;
    window.loadClientRequests = loadClientRequests;
    window.loadProfessionalsResults = loadProfessionalsResults;
    window.buyPlan = buyPlan;
    window.showProProfile = showProProfile;
    window.openProfessionalProfilePage = openProfessionalProfilePage;
})();
});
