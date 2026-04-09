// Auth initialization and state management

let currentUser = null;

function getToken() {
    return localStorage.getItem("token");
}

function getStoredRole() {
    return localStorage.getItem("role");
}

function getCurrentUser() {
    return currentUser;
}

// Initialize auth - must be called before rendering UI
async function initAuth() {
    const token = getToken();
    const role = getStoredRole();
    
    // If no token, return null (not logged in)
    if (!token) {
        return null;
    }
    
    try {
        const response = await fetch("/api/me", {
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json"
            }
        });
        
        if (!response.ok) {
            // Token invalid, clear storage
            localStorage.removeItem("token");
            localStorage.removeItem("role");
            return null;
        }
        
        const user = await response.json();
        currentUser = user;
        
        // Also store role if not already stored
        if (role !== user.role) {
            localStorage.setItem("role", user.role || "");
        }
        
        return user;
    } catch (error) {
        console.error("Auth init failed:", error);
        return null;
    }
}

// Check if user is logged in
function isLoggedIn() {
    return currentUser !== null;
}

// Get dashboard URL based on role
function getDashboardUrl() {
    if (!currentUser) return "/";
    
    const role = currentUser.role;
    if (role === 'admin') return "/admin/dashboard";
    if (role === 'professional') return "/pro/dashboard";
    return "/client/dashboard";
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
    const storedRole = getStoredRole();

    if (!token) {
        window.location.href = "/login";
        return;
    }

    if (storedRole && storedRole.toLowerCase() === expectedRole.toLowerCase()) {
        return;
    }

    localStorage.removeItem("token");
    localStorage.removeItem("role");
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
            localStorage.removeItem("token");
            localStorage.removeItem("role");
            currentUser = null;
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
        localStorage.removeItem("token");
        localStorage.removeItem("role");
        currentUser = null;
        window.location.href = "/login";
        });
    }
}
