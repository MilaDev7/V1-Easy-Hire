document.addEventListener("DOMContentLoaded", function () {
(function () {
    // Shared API helpers for all dashboards.
    function buildHeaders() {
        const headers = {
            Accept: "application/json",
        };
        const token = localStorage.getItem("token");

        if (token) {
            headers.Authorization = "Bearer " + token;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            headers["X-CSRF-TOKEN"] = csrfToken.getAttribute("content");
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

    window.EasyHireApi = {
        buildHeaders,
        fetchJson,
        postJson,
        deleteJson,
    };
})();
});
