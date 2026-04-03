<aside
    class="client-sidebar d-flex flex-column align-items-center text-center bg-white shadow-sm p-4"
    style="position: fixed; top: 0; left: 0; width: 280px; min-height: 100vh;"
>
    <div class="mb-3">
        <img
            src="{{ asset('images/user1.jpg') }}"
            alt="Client Profile Photo"
            class="rounded-circle object-fit-cover border"
            style="width: 96px; height: 96px;"
        >
    </div>

    <h2 class="h5 fw-bold mb-4" id="client-name">Client Name</h2>

    <div class="d-grid gap-2 w-100">
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="post-job"
        >
            + Post Job
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="job-posts"
        >
            Job Posts
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="professionals"
        >
            View Professionals
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="applications"
        >
            Applications
        </button>
        <button
            type="button"
            class="btn btn-outline-dark text-start sidebar-nav-button"
            data-view="contracts"
        >
            All Contracts
        </button>
    </div>

    <div class="mt-auto pt-4 w-100">
        <div class="d-grid gap-2">
            <button
                type="button"
                id="dark-mode-toggle"
                class="btn btn-outline-secondary text-start"
            >
                <i class="fa-solid fa-moon me-2"></i> Dark Mode
            </button>
            <button
                type="button"
                id="settings-button"
                class="btn btn-outline-secondary text-start"
            >
                <i class="fa-solid fa-gear me-2"></i> Settings
            </button>
        </div>
    </div>
</aside>
