import './bootstrap';

function requireAuth(proId) {
    const token = localStorage.getItem("token");

    if (!token) {
        // save where user wanted to go
        localStorage.setItem("redirect_after_login", `/professional/${proId}`);

        showAuthModal();
    } else {
        window.location.href = `/professional/${proId}`;
    }
}