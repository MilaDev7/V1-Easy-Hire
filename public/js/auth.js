// Auth initialization and state management

let currentUser = null;
let authReadyPromise = null;
const AUTH_USER_CACHE_KEY = "auth_user_cache";

function getToken() {
    return localStorage.getItem("token");
}

function getStoredRole() {
    return localStorage.getItem("role");
}

function getCurrentUser() {
    return currentUser;
}

function getCachedUser() {
    try {
        const raw = localStorage.getItem(AUTH_USER_CACHE_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw);
        return parsed && typeof parsed === "object" ? parsed : null;
    } catch (error) {
        localStorage.removeItem(AUTH_USER_CACHE_KEY);
        return null;
    }
}

function setCurrentUser(user) {
    currentUser = user || null;

    if (!user) {
        localStorage.removeItem(AUTH_USER_CACHE_KEY);
        return;
    }

    localStorage.setItem(AUTH_USER_CACHE_KEY, JSON.stringify(user));
    localStorage.setItem("role", user.role || "");
}

function clearAuthStorage() {
    localStorage.removeItem("token");
    localStorage.removeItem("role");
    localStorage.removeItem(AUTH_USER_CACHE_KEY);
    currentUser = null;
}

function getDashboardUrlFromUser(user) {
    if (!user) return "/";

    if (user.role === "admin") return "/admin/dashboard";
    if (user.role === "professional") return "/pro/dashboard";
    return "/client/dashboard";
}

function isGuestOnlyRoute() {
    const pathname = (window.location.pathname || "").replace(/\/+$/, "") || "/";
    return pathname === "/login" || pathname === "/register";
}

function redirectAuthenticatedAwayFromGuestPages(user) {
    if (!user || !isGuestOnlyRoute()) return;
    window.location.replace(getDashboardUrlFromUser(user));
}

function emitAuthChanged() {
    window.dispatchEvent(new CustomEvent("auth:changed", { detail: { user: currentUser } }));
}

// Initialize auth - must be called before rendering UI
async function initAuth(options = {}) {
    const force = options.force === true;

    if (!force && authReadyPromise) {
        return authReadyPromise;
    }

    authReadyPromise = (async () => {
        const token = getToken();

        // Token is the source of truth for auth state.
        if (!token) {
            setCurrentUser(null);
            return null;
        }

        if (!currentUser) {
            currentUser = getCachedUser();
        }

        try {
            const response = await fetch("/api/me", {
                headers: {
                    "Authorization": "Bearer " + token,
                    "Accept": "application/json"
                }
            });

            // Only clear token on true auth failures.
            if (response.status === 401 || response.status === 403) {
                clearAuthStorage();
                return null;
            }

            if (!response.ok) {
                return currentUser;
            }

            const user = await response.json();
            setCurrentUser(user);
            return user;
        } catch (error) {
            console.error("Auth init failed:", error);
            return currentUser;
        }
    })();

    const user = await authReadyPromise;
    redirectAuthenticatedAwayFromGuestPages(user);
    return user;
}

// Check if user is logged in
function isLoggedIn() {
    return currentUser !== null;
}

// Get dashboard URL based on role
function getDashboardUrl() {
    return getDashboardUrlFromUser(currentUser);
}

function requireAuth() {
    const token = getToken();

    if (!token) {
        window.location.href = "/login";
        return;
    }
}

function requireRole(expectedRole) {
    const token = getToken();
    const role = (currentUser && currentUser.role) || getStoredRole();

    if (!token) {
        window.location.href = "/login";
        return;
    }

    if (role && role.toLowerCase() === expectedRole.toLowerCase()) {
        return;
    }

    clearAuthStorage();
    window.location.href = "/login";
}

function logout() {
    const token = localStorage.getItem("token");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    
    // Call API logout to destroy session
    if (token) {
        const apiLogout = fetch("/api/logout", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json"
            }
        });

        const webLogout = fetch("/logout", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken || ""
            }
        });

        Promise.allSettled([apiLogout, webLogout]).finally(() => {
            clearAuthStorage();
            emitAuthChanged();
            window.location.href = "/login";
        });
    } else {
        fetch("/logout", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken || ""
            }
        }).finally(() => {
            clearAuthStorage();
            emitAuthChanged();
            window.location.href = "/login";
        });
    }
}

// Run once as early as possible on every page load.
initAuth().then(() => {
    emitAuthChanged();
});

// Handle browser back/forward cache restores.
window.addEventListener("pageshow", function () {
    initAuth({ force: true }).then(() => {
        emitAuthChanged();
    });
});

// Keep tabs/windows in sync.
window.addEventListener("storage", function (event) {
    if (event.key !== "token" && event.key !== "role" && event.key !== AUTH_USER_CACHE_KEY) {
        return;
    }

    initAuth({ force: true }).then(() => {
        emitAuthChanged();
    });
});

// Expose helpers for page-level scripts.
window.initAuth = initAuth;
window.getCurrentUser = getCurrentUser;
window.getDashboardUrl = getDashboardUrl;
window.requireAuth = requireAuth;
window.requireRole = requireRole;
window.logout = logout;
