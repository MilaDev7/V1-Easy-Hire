function getToken() {
    return localStorage.getItem("token");
}

function getStoredRole() {
    return localStorage.getItem("role");
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
    localStorage.removeItem("token");
    localStorage.removeItem("role");
    window.location.href = "/login";
}
