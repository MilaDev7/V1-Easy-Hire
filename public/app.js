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
            if (!response.ok) {
                throw new Error("Request failed");
            }

            return response.json();
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
                        <div class="card border-0 shadow-sm mb-4">
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
                        <div class="card border-0 shadow-sm h-100">
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

    document.addEventListener("DOMContentLoaded", function () {
        bindSidebarNavigation();
        bindJobPostsReload();
        bindDashboardTools();
        loadClientIdentity();
        handleSubscriptionAction();
        loadStats();
        loadJobPosts();
        loadSubscription();
    });

    window.loadJobPosts = loadJobPosts;
    window.loadPostJob = loadPostJob;
    window.loadProfessionals = loadProfessionals;
    window.loadApplications = loadApplications;
    window.loadAllContracts = loadAllContracts;
})();
