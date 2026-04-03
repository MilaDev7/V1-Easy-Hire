function getToken() {
    return localStorage.getItem("token");
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

    if (!token) {
        window.location.href = "/login";
        return;
    }

    fetch("/api/user", {
        headers: {
            Authorization: "Bearer " + token,
            Accept: "application/json",
        },
    })
        .then((res) => res.json())
        .then((user) => {
            if (user.role !== expectedRole) {
                alert("Unauthorized access");
                window.location.href = "/";
            }
        })
        .catch(() => {
            localStorage.removeItem("token");
            window.location.href = "/login";
        });
}

function logout() {
    localStorage.removeItem("token");
    window.location.href = "/login";
}
