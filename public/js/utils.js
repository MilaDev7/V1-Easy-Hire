document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Reusable constants used across role dashboards.
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
        "Elevator Technician",
    ];

    // Generic payload and UI helpers.
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

    function renderStars(rating) {
        const safeRating = Number(rating) || 0;
        const fullStars = Math.max(0, Math.min(5, Math.round(safeRating)));
        let stars = "";

        for (let index = 0; index < 5; index += 1) {
            stars += index < fullStars
                ? '<i class="fa-solid fa-star"></i>'
                : '<i class="fa-regular fa-star"></i>';
        }

        return stars;
    }

    window.EasyHireUtils = {
        ETHIOPIAN_CITIES,
        SKILL_OPTIONS,
        toArray,
        extractCount,
        setText,
        formatPrice,
        shortText,
        formatDate,
        renderStars,
    };
})();
});
