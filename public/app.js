(function () {
    function buildHeaders() {
        const headers = {
            Accept: "application/json",
        };
        const token = localStorage.getItem("token");

        if (token) {
            headers.Authorization = "Bearer " + token;
        }

        return headers;
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: buildHeaders(),
        }).then(async (response) => {
            if (!response.ok) {
                throw new Error("Request failed");
            }

            return response.json();
        });
    }

    function postJson(url, body) {
        const headers = buildHeaders();

        if (body !== undefined) {
            headers["Content-Type"] = "application/json";
        }

        return fetch(url, {
            method: "POST",
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        }).then(async (response) => {
            let payload = null;

            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }

            if (!response.ok) {
                throw new Error(payload?.message || "Request failed");
            }

            return payload;
        });
    }

    function deleteJson(url) {
        return fetch(url, {
            method: "DELETE",
            headers: buildHeaders(),
        }).then(async (response) => {
            let payload = null;

            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }

            if (!response.ok) {
                throw new Error(payload?.message || "Request failed");
            }

            return payload;
        });
    }

    const ETHIOPIAN_CITIES = [
        "Addis Ababa",
        "Adama",
        "Bahir Dar",
        "Hawassa",
        "Mekelle",
        "Dire Dawa",
        "Jimma",
        "Dessie",
        "Gondar",
        "Bishoftu",
        "Arba Minch",
        "Harar",
    ];

    const SKILL_OPTIONS = [
        "Electrician",
        "Plumber",
        "Carpenter",
        "Painter",
        "Welder",
        "Mason",
        "Cleaner",
        "Mechanic",
        "Tailor",
        "Driver",
        "Gardener",
        "Technician",
    ];

    function toArray(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        if (Array.isArray(payload?.data)) {
            return payload.data;
        }

        return [];
    }

    function extractCount(payload) {
        if (Array.isArray(payload)) {
            return payload.length;
        }

        if (typeof payload === "number") {
            return payload;
        }

        if (typeof payload?.count === "number") {
            return payload.count;
        }

        if (typeof payload?.total === "number") {
            return payload.total;
        }

        if (typeof payload?.remaining === "number") {
            return payload.remaining;
        }

        if (typeof payload?.data === "number") {
            return payload.data;
        }

        if (Array.isArray(payload?.data)) {
            return payload.data.length;
        }

        if (typeof payload?.active_contracts === "number") {
            return payload.active_contracts;
        }

        if (typeof payload?.job_posts_remaining === "number") {
            return payload.job_posts_remaining;
        }

        if (typeof payload?.remaining_jobs === "number") {
            return payload.remaining_jobs;
        }

        if (typeof payload?.remaining_posts === "number") {
            return payload.remaining_posts;
        }

        if (typeof payload?.total_job_posts === "number") {
            return payload.total_job_posts;
        }

        if (typeof payload?.activeContracts === "number") {
            return payload.activeContracts;
        }

        if (typeof payload?.active_contract_count === "number") {
            return payload.active_contract_count;
        }

        return 0;
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

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

        return `$${amount}`;
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
                const createdDate = formatDate(
                    jobPost.created_at || jobPost.createdAt || jobPost.date
                );

                return `
                    <tr>
                        <td class="fw-semibold">${title}</td>
                        <td>${skill}</td>
                        <td><span class="badge text-bg-light border">${status}</span></td>
                        <td>${location}</td>
                        <td>${createdDate}</td>
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
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
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

                return `
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
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

    function renderProfessionalProfile(profilePayload) {
        const contentArea = document.getElementById("content-area");

        if (!contentArea) {
            return;
        }

        const professional = profilePayload?.professional || {};
        const completedJobs = Array.isArray(profilePayload?.completed_jobs)
            ? profilePayload.completed_jobs
            : [];

        setContentHeader(
            getProfessionalName(professional),
            "Professional profile",
            false
        );

        const completedJobsMarkup = completedJobs.length
            ? completedJobs
                  .map((job) => {
                      const title =
                          job.job?.title || job.job_title || "Completed Job";
                      const status = job.job?.status || job.status || "completed";

                      return `
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                              <span>${title}</span>
                              <span class="badge text-bg-light border">${status}</span>
                          </li>
                      `;
                  })
                  .join("")
            : '<li class="list-group-item">No completed jobs found.</li>';

        contentArea.innerHTML = `
            <section class="professional-profile-section">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row gap-4 align-items-md-start">
                            <img
                                src="${getProfessionalPhoto(professional)}"
                                alt="${getProfessionalName(professional)}"
                                class="rounded-circle object-fit-cover border"
                                style="width: 96px; height: 96px;"
                            >
                            <div class="flex-grow-1">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                                    <div>
                                        <h3 class="h4 mb-1">${getProfessionalName(professional)}</h3>
                                        <p class="text-muted mb-0">${getProfessionalSkills(professional)}</p>
                                    </div>
                                    <button type="button" id="back-to-applications-button" class="btn btn-outline-dark btn-sm rounded-pill">
                                        Back to Applications
                                    </button>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <p class="text-muted small mb-1">Location</p>
                                            <p class="mb-0 fw-semibold">${getProfessionalLocation(professional)}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <p class="text-muted small mb-1">Experience</p>
                                            <p class="mb-0 fw-semibold">${getProfessionalExperience(professional)} years</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <p class="text-muted small mb-1">Rating</p>
                                            <p class="mb-0 text-warning">${renderStars(professional.average_rating ?? professional.rating ?? 0)}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted small mb-1">Bio</p>
                                    <p class="mb-0">${professional.bio || "No bio provided."}</p>
                                </div>
                                <div>
                                    <p class="text-muted small mb-2">Completed Jobs</p>
                                    <ul class="list-group list-group-flush border rounded-3">${completedJobsMarkup}</ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `;

        const backButton = document.getElementById("back-to-applications-button");

        if (backButton) {
            backButton.addEventListener("click", function () {
                loadApplications();
            });
        }
    }

    function renderProfessionalProfileError() {
        const contentArea = document.getElementById("content-area");

        if (contentArea) {
            contentArea.innerHTML =
                '<div class="alert alert-danger mb-0">Unable to load professional profile.</div>';
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
                            <div class="col-12">
                                <div id="post-job-feedback"></div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" id="submit-post-job-button" class="btn btn-success rounded-pill px-4">
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

            if (budgetValue) {
                payload.budget = budgetValue;
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

    function getContractProfessionalName(contract) {
        return (
            contract.professional_name ||
            contract.professional?.name ||
            contract.professional?.full_name ||
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

                return `
                    <tr>
                        <td class="fw-semibold">${getContractTitle(contract)}</td>
                        <td>${getContractClientName(contract)}</td>
                        <td>${getContractProfessionalName(contract)}</td>
                        <td><span class="badge text-bg-light border">${status}</span></td>
                        <td>${createdDate}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger contract-action-button"
                                    data-action="cancel"
                                    data-contract-id="${contract.id}"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success contract-action-button"
                                    data-action="confirm"
                                    data-contract-id="${contract.id}"
                                >
                                    Confirm
                                </button>
                            </div>
                        </td>
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
                            <th scope="col">Professional Name</th>
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
                                >
                                    Accept
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger application-action-button"
                                    data-action="reject"
                                    data-application-id="${application.id}"
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

    function getSubscriptionPlanId(subscription) {
        return subscription.plan_id || subscription.id || subscription.plan?.id || "";
    }

    function getSubscriptionPlanName(subscription) {
        return (
            subscription.plan_name ||
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
            subscription.plan?.job_post_limit ||
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

        const actionButton = document.getElementById("subscription-action-button");

        if (actionButton) {
            actionButton.dataset.planId = getSubscriptionPlanId(subscription);
        }
    }

    function renderSubscriptionError() {
        setText("subscription-plan-name", "Plan unavailable");
        setText("subscription-plan-price", "N/A");
        setText("subscription-job-post-limit", "N/A");
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

            fetchJson(`/api/buy-plan/${planId}`).catch(() => null);
        });
    }

    function loadSubscription() {
        fetchJson("/api/client/my-subscription")
            .then((payload) => {
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

    function loadProfessionalProfile(profileId) {
        setActiveSidebarButton("applications");
        setContentHeader("Professional Profile", "Loading professional details...", false);
        const contentArea = document.getElementById("content-area");

        if (contentArea) {
            contentArea.innerHTML = '<div class="text-muted">Loading professional profile...</div>';
        }

        fetchJson(`/api/professionals/${profileId}`)
            .then((payload) => {
                renderProfessionalProfile(payload);
            })
            .catch(() => {
                renderProfessionalProfileError();
            });
    }

    function bindProfessionalProfileLinks() {
        document.querySelectorAll(".professional-profile-link").forEach((link) => {
            link.addEventListener("click", function () {
                const profileId = link.dataset.profileId;

                if (profileId) {
                    loadProfessionalProfile(profileId);
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
                    })
                    .catch(() => {
                        button.disabled = false;
                        window.alert(`Unable to ${action} application.`);
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
                    openContractConfirmModal(contractId);
                    return;
                }

                button.disabled = true;

                postJson(`/api/contracts/${contractId}/${action}`)
                    .then(() => {
                        loadAllContracts();
                        loadStats();
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
                            <h5 class="modal-title">Confirm Contract</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="contract-confirm-form">
                            <div class="modal-body">
                                <input type="hidden" id="contract-confirm-id">
                                <div class="mb-3">
                                    <label for="contract-rating" class="form-label fw-semibold">Rating</label>
                                    <select id="contract-rating" class="form-select" required>
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

    function openContractConfirmModal(contractId) {
        const modalElement = ensureContractConfirmModal();
        const contractIdInput = document.getElementById("contract-confirm-id");
        const ratingInput = document.getElementById("contract-rating");
        const reviewInput = document.getElementById("contract-review-comment");
        const reportInput = document.getElementById("contract-report-reason");
        const feedback = document.getElementById("contract-confirm-feedback");

        if (!modalElement || !contractIdInput || !ratingInput || !reviewInput || !reportInput || !feedback) {
            return;
        }

        contractIdInput.value = contractId;
        ratingInput.value = "";
        reviewInput.value = "";
        reportInput.value = "";
        feedback.innerHTML = "";

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
            const rating = document.getElementById("contract-rating")?.value;
            const comment = document.getElementById("contract-review-comment")?.value || "";
            const reportReason = document.getElementById("contract-report-reason")?.value || "";
            const feedback = document.getElementById("contract-confirm-feedback");
            const submitButton = document.getElementById("contract-confirm-submit");
            const modalElement = document.getElementById("contract-confirm-modal");

            if (!contractId || !rating || !feedback || !submitButton || !modalElement) {
                return;
            }

            submitButton.disabled = true;
            feedback.innerHTML = '<div class="text-muted">Submitting confirmation...</div>';

            postJson(`/api/contracts/${contractId}/confirm`)
                .then(() =>
                    postJson(`/api/contracts/${contractId}/review`, {
                        rating: Number(rating),
                        comment,
                    })
                )
                .then(() => {
                    if (!reportReason.trim()) {
                        return null;
                    }

                    return postJson(`/api/contracts/${contractId}/report`, {
                        reason: reportReason.trim(),
                    });
                })
                .then(() => {
                    feedback.innerHTML =
                        '<div class="alert alert-success mb-0">Contract confirmed successfully.</div>';
                    loadAllContracts();
                    loadStats();

                    setTimeout(() => {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modal.hide();
                    }, 700);
                })
                .catch(() => {
                    submitButton.disabled = false;
                    feedback.innerHTML =
                        '<div class="alert alert-danger mb-0">Unable to confirm contract, review, or report.</div>';
                });
        });
    }

    function setActiveSidebarButton(view) {
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

    function bindDashboardTools() {
        const darkModeToggle = document.getElementById("dark-mode-toggle");
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

        if (settingsButton) {
            settingsButton.addEventListener("click", function () {
                if (settingsModal) {
                    settingsModal.show();
                }
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

        if (!clientNameElement && !sidebarPhotoElement) {
            return;
        }

        fetchJson("/api/client/me")
            .then((client) => {
                if (clientNameElement) {
                    clientNameElement.textContent = client.name || "Client Name";
                }

                if (sidebarPhotoElement && client.profile_photo) {
                    sidebarPhotoElement.src = client.profile_photo;
                }
            })
            .catch(() => {
                if (clientNameElement) {
                    clientNameElement.textContent = "Client Name";
                }
            });
    }

    function loadStats() {
        Promise.all([
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

        fetchJson("/api/client/job-posts")
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
        bindProfessionalSearch();
        loadProfessionalsResults();
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

                if (button.disabled) {
                    return;
                }

                button.disabled = true;
                button.textContent = "Applying...";
                clearProfessionalFeedback();

                postJson("/api/pro/apply", {
                    job_id: Number(jobId),
                })
                    .then(() => {
                        button.className = "btn btn-secondary professional-apply-button";
                        button.dataset.hasApplied = "true";
                        button.textContent = "Applied";
                        showProfessionalFeedback("success", "Application submitted successfully.");
                        loadProfessionalStats();
                    })
                    .catch((error) => {
                        const message = error.message || "Unable to apply for this job.";

                        if (message.toLowerCase().includes("already applied")) {
                            button.disabled = true;
                            button.dataset.hasApplied = "true";
                            button.className = "btn btn-secondary professional-apply-button";
                            button.textContent = "Applied";
                            showProfessionalApplyInvalidModal("You already applied for this job.");
                            return;
                        }

                        if (message.toLowerCase().includes("skill mismatch")) {
                            button.disabled = false;
                            button.className = "btn btn-dark professional-apply-button";
                            button.textContent = "Apply";
                            showProfessionalApplyInvalidModal("You cannot apply outside your skill.");
                            return;
                        }

                        button.disabled = false;
                        button.className = "btn btn-dark professional-apply-button";
                        button.textContent = "Apply";
                        showProfessionalFeedback("danger", message);
                    });
            });
        });
    }

    function loadProfessionalIdentity() {
        fetchJson("/api/pro/me")
            .then((profile) => {
                setText("professional-dashboard-name", profile.name || "Professional");
                setText("professional-sidebar-skill", profile.skill || "Skill");
                setText("professional-sidebar-location", profile.location || "Not set");
                
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
                        rateElement.innerHTML = '<span class="text-muted">No rating</span>';
                    }
                }
                
                setProfessionalStatus(profile.approval_status);

                const photo = document.getElementById("professional-dashboard-photo");

                if (photo && profile.profile_photo) {
                    photo.src = profile.profile_photo;
                }
            })
            .catch(() => {
                setText("professional-dashboard-name", "Professional");
                setText("professional-sidebar-skill", "Skill");
                setText("professional-sidebar-location", "Not set");
                const rateElement = document.getElementById("professional-sidebar-rate");
                if (rateElement) {
                    rateElement.innerHTML = '<span class="text-muted">No rating</span>';
                }
                setProfessionalStatus("pending");
            });
    }

    function loadProfessionalStats() {
        fetchJson("/api/pro/stats")
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

        fetchJson(`/api/jobs${params.toString() ? `?${params.toString()}` : ""}`)
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

                fetch("/api/pro/withdraw-application", {
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
                    .then(() => {
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

                fetch("/api/pro/complete-contract", {
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

                loadProfessionalJobsView();
            });
        });
    }

    function bindProfessionalSettings() {
        const settingsButton = document.getElementById("professional-settings-button");
        const settingsModalElement = document.getElementById("professional-settings-modal");
        const darkModeToggle = document.getElementById("professional-dark-mode-toggle");
        const darkModeLabel = document.getElementById("professional-dark-mode-label");
        const deleteAccountButton = document.getElementById("professional-delete-account-button");
        const settingsModal = settingsModalElement
            ? bootstrap.Modal.getOrCreateInstance(settingsModalElement)
            : null;

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
                deleteAccountButton.disabled = true;
                deleteAccountButton.textContent = "Deleting...";

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
                        deleteAccountButton.disabled = false;
                        deleteAccountButton.innerHTML =
                            '<i class="fa-solid fa-trash me-1"></i> Delete Account';
                    });
            });
        }
    }

    function loadProfessionalJobsView() {
        setActiveProfessionalNav("browse-jobs");
        clearProfessionalFeedback();
        renderProfessionalJobsSection();
        loadProfessionalJobs();
    }

    function initializeProfessionalDashboard() {
        bindProfessionalSidebarNavigation();
        bindProfessionalSettings();
        loadProfessionalIdentity();
        loadProfessionalStats();
        loadProfessionalJobsView();
    }

    function loadAdminStats() {
        console.log("Loading admin stats...");
        fetchJson("/api/admin/stats")
            .then((payload) => {
                console.log("Stats payload:", payload);
                setText("admin-pending-approvals-count", payload.pending_professionals ?? 0);
                setText("admin-active-contracts-count", payload.active_contracts ?? 0);
                setText("admin-total-users-count", payload.total_users ?? 0);
                setText("admin-open-reports-count", payload.open_reports ?? 0);
            })
            .catch((err) => {
                console.error("Error loading stats:", err);
                setText("admin-pending-approvals-count", "--");
                setText("admin-active-contracts-count", "--");
                setText("admin-total-users-count", "--");
                setText("admin-open-reports-count", "--");
            });
    }

    function loadPendingProfessionals() {
        fetchJson("/api/admin/professionals/pending")
            .then((payload) => {
                renderPendingProfessionals(toArray(payload));
            })
            .catch(() => {
                renderPendingProfessionalsError();
            });
    }

    function renderPendingProfessionals(professionals) {
        const area = document.getElementById("admin-pending-professionals-area");

        if (!area) {
            return;
        }

        if (!professionals.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No pending professionals.</div>';
            return;
        }

        const rows = professionals
            .map((pro) => {
                const photo = pro.profile_photo 
                    ? `/storage/${pro.profile_photo}` 
                    : "/images/user1.jpg";
                
                return `
                    <tr data-professional-id="${pro.id}">
                        <td>
                            <img src="${photo}" alt="Photo" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                        </td>
                        <td class="fw-semibold">${pro.user?.name ?? 'N/A'}</td>
                        <td>${pro.skill ?? 'N/A'}</td>
                        <td>${pro.location ?? 'N/A'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewDocument('cv', ${pro.id})">
                                <i class="fa-solid fa-file me-1"></i> CV
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="viewDocument('certificate', ${pro.id})">
                                <i class="fa-solid fa-certificate me-1"></i> Certificate
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="viewDocument('id', ${pro.id})">
                                <i class="fa-solid fa-id-card me-1"></i> ID
                            </button>
                        </td>
                        <td>
                            <span class="badge bg-warning">Pending</span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-success" onclick="approveProfessional(${pro.id})">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="rejectProfessional(${pro.id})">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            })
            .join("");

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Photo</th>
                            <th scope="col">Name</th>
                            <th scope="col">Skill</th>
                            <th scope="col">Location</th>
                            <th scope="col">CV</th>
                            <th scope="col">Certificate</th>
                            <th scope="col">ID</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderPendingProfessionalsError() {
        const area = document.getElementById("admin-pending-professionals-area");

        if (area) {
            area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load pending professionals.</div>';
        }
    }

    function approveProfessional(id) {
        showAdminActionModal(
            'Approve Professional',
            'Are you sure you want to approve this professional?',
            'success',
            () => {
                postJson(`/api/admin/professionals/${id}/approve`)
                    .then(() => {
                        removePendingRow(id);
                        loadAdminStats();
                    })
                    .catch((err) => {
                        alert(err.message || "Failed to approve professional.");
                    });
            }
        );
    }

    function rejectProfessional(id) {
        showAdminActionModal(
            'Reject Professional',
            'Are you sure you want to reject this professional?',
            'danger',
            () => {
                postJson(`/api/admin/professionals/${id}/reject`)
                    .then(() => {
                        removePendingRow(id);
                        loadAdminStats();
                    })
                    .catch((err) => {
                        alert(err.message || "Failed to reject professional.");
                    });
            }
        );
    }

    function removePendingRow(id) {
        const row = document.querySelector(`tr[data-professional-id="${id}"]`);
        if (row) {
            row.remove();
        }
        const tbody = document.querySelector('#admin-pending-professionals-area tbody');
        if (tbody && !tbody.children.length) {
            const area = document.getElementById('admin-pending-professionals-area');
            area.innerHTML = '<div class="alert alert-light border mb-0">No pending professionals.</div>';
        }
    }

    function showAdminActionModal(title, message, btnClass, onConfirm) {
        const modalEl = document.getElementById('admin-action-modal');
        const titleEl = document.getElementById('admin-action-modal-title');
        const bodyEl = document.getElementById('admin-action-modal-body');
        const confirmBtn = document.getElementById('admin-action-confirm-btn');

        titleEl.textContent = title;
        bodyEl.textContent = message;
        confirmBtn.className = `btn btn-${btnClass}`;
        confirmBtn.textContent = 'Confirm';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        confirmBtn.onclick = () => {
            modal.hide();
            onConfirm();
        };
    }

    function viewDocument(type, id) {
        window.open(`/api/admin/professionals/${id}/document/${type}`, '_blank');
    }

    function initializeAdminSidebar() {
        const sidebarItems = document.querySelectorAll('.admin-sidebar-item');
        const sidebarChildren = document.querySelectorAll('.admin-sidebar-child');

        let currentOpen = null;

        sidebarItems.forEach(item => {
            item.addEventListener('click', function() {
                const toggleId = this.dataset.toggle;
                const view = this.dataset.view;

                if (toggleId) {
                    const children = document.getElementById(toggleId);
                    const icon = this.querySelector('.admin-collapse-icon');

                    if (currentOpen && currentOpen !== children) {
                        currentOpen.classList.remove('show');
                        const prevItem = document.querySelector(`[data-toggle="${currentOpen.id}"]`);
                        if (prevItem) {
                            prevItem.querySelector('.admin-collapse-icon').classList.remove('rotated');
                        }
                    }

                    if (children) {
                        children.classList.toggle('show');
                        icon.classList.toggle('rotated');
                        currentOpen = children.classList.contains('show') ? children : null;
                    }
                }

                if (view) {
                    loadAdminSection(view);
                }
            });
        });

        sidebarChildren.forEach(child => {
            child.addEventListener('click', function(e) {
                e.stopPropagation();
                const view = this.dataset.view;

                document.querySelectorAll('.admin-sidebar-child').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                if (view) {
                    loadAdminSection(view);
                }
            });
        });
    }

    function loadAdminSection(view) {
        document.querySelectorAll('.admin-sidebar-item').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.admin-sidebar-child').forEach(child => child.classList.remove('active'));

        const item = document.querySelector(`.admin-sidebar-item[data-view="${view}"]`);
        const child = document.querySelector(`.admin-sidebar-child[data-view="${view}"]`);

        if (item) item.classList.add('active');
        if (child) child.classList.add('active');

        const pendingSection = document.querySelector('.admin-content-section:not(#users-section):not(#jobs-section):not(#contracts-section):not(#reports-section):not(#plans-section)');
        const usersSection = document.getElementById('users-section');
        const jobsSection = document.getElementById('jobs-section');
        const contractsSection = document.getElementById('contracts-section');
        const reportsSection = document.getElementById('reports-section');
        const plansSection = document.getElementById('plans-section');
        
        const contentArea = document.getElementById('admin-content-area');
        
        const sections = {
            'all-users': { title: 'All Users', fetch: loadUsers },
            'suspended-users': { title: 'Suspended Users', fetch: loadSuspendedUsers },
            'deleted-users': { title: 'Deleted Users', fetch: loadDeletedUsers },
            'all-professionals': { title: 'All Professionals', fetch: loadAllProfessionals },
            'pending-professionals': { title: 'Pending Approvals', fetch: loadPendingProfessionals },
            'jobs-view': { title: 'Jobs', fetch: loadJobs },
            'contracts-view': { title: 'Contracts', fetch: loadContracts },
            'reports': { title: 'All Reports', fetch: loadReports },
            'resolved-reports': { title: 'Resolved Reports', fetch: loadResolvedReports },
            'all-payments': { title: 'All Payments', fetch: loadAllPayments },
            'pending-payments': { title: 'Pending Payments', fetch: loadPendingPayments },
            'plans': { title: 'Subscription Plans', fetch: loadPlans },
            'general-settings': { title: 'General Settings', fetch: loadGeneralSettings },
            'system-settings': { title: 'System Settings', fetch: loadSystemSettings }
        };

        if (sections[view]) {
            hideAllAdminSections();
            if (['all-users', 'suspended-users', 'deleted-users'].includes(view) && usersSection) {
                usersSection.style.display = 'block';
                document.getElementById('users-section-title').textContent = sections[view].title;
            } else if (view === 'pending-professionals' && pendingSection) {
                pendingSection.style.display = 'block';
                if (contentArea) { contentArea.innerHTML = ''; contentArea.appendChild(pendingSection); }
            } else if (['jobs-view'].includes(view) && jobsSection) {
                jobsSection.style.display = 'block';
            } else if (['contracts-view'].includes(view) && contractsSection) {
                contractsSection.style.display = 'block';
            } else if (['reports', 'resolved-reports'].includes(view) && reportsSection) {
                reportsSection.style.display = 'block';
            } else if (['plans'].includes(view) && plansSection) {
                plansSection.style.display = 'block';
            }
            sections[view].fetch();
        } else {
            hideAllAdminSections();
        }
    }

    function loadAllProfessionals() { fetchJson("/api/admin/professionals").then(p => renderProfessionalsTable(toArray(p))).catch(renderProfessionalsTableError); }
    function renderProfessionalsTable(pros) {
        const area = document.getElementById('users-table-area');
        if (!area) return;
        if (!pros.length) { area.innerHTML = '<div class="alert alert-light border mb-0">No professionals found.</div>'; return; }
        area.innerHTML = '<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Skill</th><th>Location</th><th>Status</th></tr></thead><tbody>' + 
            pros.map(p => `<tr><td class="fw-semibold">${p.user?.name ?? 'N/A'}</td><td>${p.skill ?? 'N/A'}</td><td>${p.location ?? 'N/A'}</td><td><span class="badge bg-${p.approval_status === 'approved' ? 'success' : 'warning'}">${p.approval_status ?? 'Pending'}</span></td></tr>`).join('') + 
            '</tbody></table></div>';
    }
    function renderProfessionalsTableError() {
        const area = document.getElementById('users-table-area');
        if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load professionals.</div>';
    }

    function loadResolvedReports() { fetchJson("/api/admin/reports?status=resolved").then(p => renderReportsTable(toArray(p))).catch(renderReportsTableError); }
    function loadAllPayments() { 
        const area = document.getElementById('users-table-area');
        if (area) area.innerHTML = '<div class="alert alert-info mb-0">Payments section coming soon.</div>';
    }
    function loadPendingPayments() { 
        const area = document.getElementById('users-table-area');
        if (area) area.innerHTML = '<div class="alert alert-info mb-0">Pending payments section coming soon.</div>';
    }
    function loadGeneralSettings() { 
        const area = document.getElementById('users-table-area');
        if (area) area.innerHTML = '<div class="alert alert-info mb-0">General settings section coming soon.</div>';
    }
    function loadSystemSettings() { 
        const area = document.getElementById('users-table-area');
        if (area) area.innerHTML = '<div class="alert alert-info mb-0">System settings section coming soon.</div>';
    }

    function toggleAdminDarkMode() {
        console.log("Dark mode toggle clicked!");
        document.body.classList.toggle('admin-dashboard-dark');
        
        // Save preference
        const isDark = document.body.classList.contains('admin-dashboard-dark');
        localStorage.setItem('admin_dark_mode', isDark ? '1' : '0');
        
        console.log("Dark mode active:", isDark);
    }

    // Load saved dark mode preference on page load
    function loadAdminDarkModePreference() {
        const savedPref = localStorage.getItem('admin_dark_mode');
        if (savedPref === '1') {
            document.body.classList.add('admin-dashboard-dark');
        }
    }

    function hideAllAdminSections() {
        document.querySelectorAll('.admin-content-section').forEach(el => el.style.display = 'none');
    }

    function loadUsers() {
        fetchJson("/api/admin/users")
            .then((payload) => {
                renderUsersTable(toArray(payload), 'all');
            })
            .catch(() => {
                renderUsersTableError();
            });
    }

    function loadSuspendedUsers() {
        fetchJson("/api/admin/users?suspended=1")
            .then((payload) => {
                renderUsersTable(toArray(payload), 'suspended');
            })
            .catch(() => {
                renderUsersTableError();
            });
    }

    function loadDeletedUsers() {
        fetchJson("/api/admin/users?deleted=1")
            .then((payload) => {
                renderUsersTable(toArray(payload), 'deleted');
            })
            .catch(() => {
                renderUsersTableError();
            });
    }

    function renderUsersTable(users, type) {
        const area = document.getElementById('users-table-area');
        if (!area) return;

        if (!users.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No users found.</div>';
            return;
        }

        const rows = users.map(user => {
            const statusBadge = user.deleted_at 
                ? '<span class="badge bg-danger">Deleted</span>'
                : user.is_suspended 
                    ? '<span class="badge bg-warning">Suspended</span>'
                    : '<span class="badge bg-success">Active</span>';
            
            const roleBadge = user.role === 'admin' 
                ? '<span class="badge bg-primary">Admin</span>'
                : user.role === 'professional'
                    ? '<span class="badge bg-info">Professional</span>'
                    : '<span class="badge bg-secondary">Client</span>';

            return `
                <tr data-user-id="${user.id}">
                    <td class="fw-semibold">${user.name ?? 'N/A'}</td>
                    <td>${user.email ?? 'N/A'}</td>
                    <td>${roleBadge}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="d-flex gap-2">
                            ${!user.deleted_at && !user.is_suspended ? `<button type="button" class="btn btn-sm btn-warning" onclick="suspendUser(${user.id})"><i class="fa-solid fa-ban"></i></button>` : ''}
                            ${user.is_suspended && !user.deleted_at ? `<button type="button" class="btn btn-sm btn-success" onclick="unsuspendUser(${user.id})"><i class="fa-solid fa-check"></i></button>` : ''}
                            ${user.deleted_at ? `<button type="button" class="btn btn-sm btn-info" onclick="restoreUser(${user.id})"><i class="fa-solid fa-trash-restore"></i></button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderUsersTableError() {
        const area = document.getElementById('users-table-area');
        if (area) {
            area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load users.</div>';
        }
    }

    function removeUserRow(id) {
        const row = document.querySelector(`tr[data-user-id="${id}"]`);
        if (row) row.remove();
    }

    function showUsersActionModal(title, message, btnClass, onConfirm) {
        const modalEl = document.getElementById('users-action-modal');
        const titleEl = document.getElementById('users-action-modal-title');
        const bodyEl = document.getElementById('users-action-modal-body');
        const confirmBtn = document.getElementById('users-action-confirm-btn');

        titleEl.textContent = title;
        bodyEl.textContent = message;
        confirmBtn.className = `btn btn-${btnClass}`;
        confirmBtn.textContent = 'Confirm';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        confirmBtn.onclick = () => {
            modal.hide();
            onConfirm();
        };
    }

    function suspendUser(id) {
        showUsersActionModal(
            'Suspend User',
            'Are you sure you want to suspend this user?',
            'warning',
            () => {
                postJson(`/api/admin/users/${id}/suspend`)
                    .then(() => {
                        removeUserRow(id);
                        loadAdminStats();
                    })
                    .catch(err => alert(err.message || 'Failed to suspend user.'));
            }
        );
    }

    function unsuspendUser(id) {
        showUsersActionModal(
            'Unsuspend User',
            'Are you sure you want to unsuspend this user?',
            'success',
            () => {
                postJson(`/api/admin/users/${id}/unsuspend`)
                    .then(() => {
                        removeUserRow(id);
                        loadAdminStats();
                    })
                    .catch(err => alert(err.message || 'Failed to unsuspend user.'));
            }
        );
    }

    function restoreUser(id) {
        showUsersActionModal(
            'Restore User',
            'Are you sure you want to restore this user?',
            'info',
            () => {
                postJson(`/api/admin/users/${id}/restore`)
                    .then(() => {
                        removeUserRow(id);
                        loadAdminStats();
                    })
                    .catch(err => alert(err.message || 'Failed to restore user.'));
            }
        );
    }

    function loadJobs() {
        fetchJson("/api/admin/jobs")
            .then((payload) => {
                renderJobsTable(toArray(payload));
            })
            .catch(() => {
                renderJobsTableError();
            });
    }

    function renderJobsTable(jobs) {
        const area = document.getElementById('jobs-table-area');
        if (!area) return;

        if (!jobs.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No jobs found.</div>';
            return;
        }

        const rows = jobs.map(job => {
            const statusBadge = job.status === 'open' 
                ? '<span class="badge bg-success">Open</span>'
                : job.status === 'completed'
                    ? '<span class="badge bg-info">Completed</span>'
                    : job.status === 'cancelled'
                        ? '<span class="badge bg-danger">Cancelled</span>'
                        : '<span class="badge bg-warning">Pending</span>';

            return `
                <tr data-job-id="${job.id}">
                    <td class="fw-semibold">${job.title ?? 'N/A'}</td>
                    <td>${job.skill ?? 'N/A'}</td>
                    <td>${job.location ?? 'N/A'}</td>
                    <td>${job.budget ? '$' + job.budget : 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${job.client?.name ?? 'N/A'}</td>
                    <td>${job.created_at ? new Date(job.created_at).toLocaleDateString() : 'N/A'}</td>
                </tr>
            `;
        }).join('');

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Skill</th>
                            <th scope="col">Location</th>
                            <th scope="col">Budget</th>
                            <th scope="col">Status</th>
                            <th scope="col">Client</th>
                            <th scope="col">Created</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderJobsTableError() {
        const area = document.getElementById('jobs-table-area');
        if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load jobs.</div>';
    }

    function loadContracts() {
        fetchJson("/api/admin/contracts")
            .then((payload) => {
                renderContractsTable(toArray(payload));
            })
            .catch(() => {
                renderContractsTableError();
            });
    }

    function renderContractsTable(contracts) {
        const area = document.getElementById('contracts-table-area');
        if (!area) return;

        if (!contracts.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No contracts found.</div>';
            return;
        }

        const rows = contracts.map(contract => {
            const statusBadge = contract.status === 'active' 
                ? '<span class="badge bg-success">Active</span>'
                : contract.status === 'completed'
                    ? '<span class="badge bg-info">Completed</span>'
                    : contract.status === 'cancelled'
                        ? '<span class="badge bg-danger">Cancelled</span>'
                        : '<span class="badge bg-warning">Pending</span>';

            const canCancel = contract.status === 'active';

            return `
                <tr data-contract-id="${contract.id}">
                    <td class="fw-semibold">${contract.title ?? 'N/A'}</td>
                    <td>${contract.job?.title ?? 'N/A'}</td>
                    <td>${contract.client?.name ?? 'N/A'}</td>
                    <td>${contract.professional?.name ?? 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${contract.created_at ? new Date(contract.created_at).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        ${canCancel ? `<button type="button" class="btn btn-sm btn-danger" onclick="forceCancelContract(${contract.id})"><i class="fa-solid fa-times"></i> Force Cancel</button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Job</th>
                            <th scope="col">Client</th>
                            <th scope="col">Professional</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderContractsTableError() {
        const area = document.getElementById('contracts-table-area');
        if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load contracts.</div>';
    }

    function removeContractRow(id) {
        const row = document.querySelector(`tr[data-contract-id="${id}"]`);
        if (row) row.remove();
    }

    function forceCancelContract(id) {
        const modalEl = document.getElementById('admin-contract-modal');
        const titleEl = document.getElementById('admin-contract-modal-title');
        const bodyEl = document.getElementById('admin-contract-modal-body');
        const confirmBtn = document.getElementById('admin-contract-confirm-btn');

        titleEl.textContent = 'Force Cancel Contract';
        bodyEl.textContent = 'Are you sure you want to force cancel this contract? This action cannot be undone.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.textContent = 'Force Cancel';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        confirmBtn.onclick = () => {
            modal.hide();
            postJson(`/api/admin/contracts/${id}/force-cancel`)
                .then(() => {
                    removeContractRow(id);
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to cancel contract.'));
        };
    }

    let currentResolveReportId = null;

    function loadReports() {
        fetchJson("/api/admin/reports")
            .then((payload) => {
                renderReportsTable(toArray(payload));
            })
            .catch(() => {
                renderReportsTableError();
            });
    }

    function renderReportsTable(reports) {
        const area = document.getElementById('reports-table-area');
        if (!area) return;

        if (!reports.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No reports found.</div>';
            return;
        }

        const rows = reports.map(report => {
            const statusBadge = report.status === 'resolved' 
                ? '<span class="badge bg-success">Resolved</span>'
                : '<span class="badge bg-warning">Pending</span>';

            return `
                <tr data-report-id="${report.id}">
                    <td class="fw-semibold">${report.title ?? 'N/A'}</td>
                    <td>${report.reporter?.name ?? 'N/A'}</td>
                    <td>${report.reported?.name ?? 'N/A'}</td>
                    <td>${report.reason ?? 'N/A'}</td>
                    <td>${statusBadge}</td>
                    <td>${report.created_at ? new Date(report.created_at).toLocaleDateString() : 'N/A'}</td>
                    <td>
                        ${report.status !== 'resolved' ? `<button type="button" class="btn btn-sm btn-success" onclick="showResolveReportModal(${report.id})"><i class="fa-solid fa-check"></i> Resolve</button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Reporter</th>
                            <th scope="col">Reported</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderReportsTableError() {
        const area = document.getElementById('reports-table-area');
        if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load reports.</div>';
    }

    function showResolveReportModal(id) {
        currentResolveReportId = id;
        document.getElementById('report-resolution-notes').value = '';
        const modal = new bootstrap.Modal(document.getElementById('admin-report-modal'));
        modal.show();

        document.getElementById('report-resolve-confirm-btn').onclick = () => {
            const notes = document.getElementById('report-resolution-notes').value;
            postJson(`/api/admin/reports/${id}/resolve`, { notes })
                .then(() => {
                    modal.hide();
                    loadReports();
                    loadAdminStats();
                })
                .catch(err => alert(err.message || 'Failed to resolve report.'));
        };
    }

    function loadPlans() {
        fetchJson("/api/admin/plans")
            .then((payload) => {
                renderPlansTable(toArray(payload));
            })
            .catch(() => {
                renderPlansTableError();
            });
    }

    function renderPlansTable(plans) {
        const area = document.getElementById('plans-table-area');
        if (!area) return;

        if (!plans.length) {
            area.innerHTML = '<div class="alert alert-light border mb-0">No plans found.</div>';
            return;
        }

        const rows = plans.map(plan => `
            <tr data-plan-id="${plan.id}">
                <td class="fw-semibold">${plan.name ?? 'N/A'}</td>
                <td>$${plan.price ?? 0}</td>
                <td>${plan.duration_days ?? 0} days</td>
                <td>${plan.job_posts_allowed ?? 0}</td>
                <td>${plan.description ?? '-'}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary" onclick="showEditPlanModal(${plan.id}, '${plan.name}', ${plan.price}, ${plan.duration_days}, ${plan.job_posts_allowed}, '${plan.description || ''}')">
                            <i class="fa-solid fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="showDeletePlanModal(${plan.id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        area.innerHTML = `
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Job Posts</th>
                            <th scope="col">Description</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    function renderPlansTableError() {
        const area = document.getElementById('plans-table-area');
        if (area) area.innerHTML = '<div class="alert alert-danger mb-0">Unable to load plans.</div>';
    }

    let currentDeletePlanId = null;

    function showCreatePlanModal() {
        document.getElementById('plan-id').value = '';
        document.getElementById('plan-name').value = '';
        document.getElementById('plan-price').value = '';
        document.getElementById('plan-duration').value = '';
        document.getElementById('plan-job-posts').value = '';
        document.getElementById('plan-description').value = '';
        document.getElementById('admin-plan-modal-title').textContent = 'Create Plan';
        new bootstrap.Modal(document.getElementById('admin-plan-modal')).show();
    }

    function showEditPlanModal(id, name, price, duration, jobPosts, description) {
        document.getElementById('plan-id').value = id;
        document.getElementById('plan-name').value = name;
        document.getElementById('plan-price').value = price;
        document.getElementById('plan-duration').value = duration;
        document.getElementById('plan-job-posts').value = jobPosts;
        document.getElementById('plan-description').value = description;
        document.getElementById('admin-plan-modal-title').textContent = 'Edit Plan';
        new bootstrap.Modal(document.getElementById('admin-plan-modal')).show();
    }

    function savePlan() {
        const id = document.getElementById('plan-id').value;
        const data = {
            name: document.getElementById('plan-name').value,
            price: parseFloat(document.getElementById('plan-price').value),
            duration_days: parseInt(document.getElementById('plan-duration').value),
            job_posts_allowed: parseInt(document.getElementById('plan-job-posts').value),
            description: document.getElementById('plan-description').value
        };

        const url = id ? `/api/admin/plans/${id}` : '/api/admin/plans';
        const method = id ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('token') },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('admin-plan-modal')).hide();
            loadPlans();
        })
        .catch(err => alert(err.message || 'Failed to save plan.'));
    }

    function showDeletePlanModal(id) {
        currentDeletePlanId = id;
        const modal = new bootstrap.Modal(document.getElementById('admin-plan-delete-modal'));
        modal.show();

        document.getElementById('plan-delete-confirm-btn').onclick = () => {
            modal.hide();
            fetch(`/api/admin/plans/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
            })
            .then(() => {
                document.querySelector(`tr[data-plan-id="${id}"]`)?.remove();
                loadPlans();
            })
            .catch(err => alert(err.message || 'Failed to delete plan.'));
        };
    }

    document.addEventListener("DOMContentLoaded", function () {
        const clientDashboard = document.querySelector(".client-dashboard-main");
        const professionalDashboard = document.getElementById("professional-dashboard");
        const adminDashboard = document.getElementById("admin-dashboard");

        if (clientDashboard) {
            bindSidebarNavigation();
            bindJobPostsReload();
            bindDashboardTools();
            loadClientIdentity();
            handleSubscriptionAction();
            loadStats();
            loadJobPosts();
            loadSubscription();
        }

        if (professionalDashboard) {
            initializeProfessionalDashboard();
        }

        if (adminDashboard) {
            initializeAdminSidebar();
            loadAdminStats();
            loadPendingProfessionals();
        }
    });

    window.loadJobPosts = loadJobPosts;
    window.loadPostJob = loadPostJob;
    window.loadProfessionals = loadProfessionals;
    window.loadApplications = loadApplications;
    window.loadAllContracts = loadAllContracts;
    window.loadProfessionalJobs = loadProfessionalJobs;
    window.bindProfessionalSearch = bindProfessionalSearch;
    window.loadProfessionalsResults = loadProfessionalsResults;
    window.loadAdminStats = loadAdminStats;
    window.loadPendingProfessionals = loadPendingProfessionals;
    window.toggleAdminDarkMode = toggleAdminDarkMode;
})();
