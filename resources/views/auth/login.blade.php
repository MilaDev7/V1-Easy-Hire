@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0 p-4">
                <h4 class="fw-bold text-center mb-4">Login</h4>
                <div class="mb-3">
                    <input type="email" id="loginEmail" class="form-control" placeholder="Email">
                </div>
                <div class="mb-3 position-relative">
                    <input type="password" id="loginPass" class="form-control" placeholder="Password">
                    <button type="button" class="btn btn-sm position-absolute end-0 top-0 pe-2 pt-2" onclick="togglePassword('loginPass', this)">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <button onclick="handleLogin()" class="btn btn-dark w-100 py-2 fw-bold" data-testid="login-submit">Sign In</button>
            </div>
        </div>
    </div>
</div>
@endsection


<!-- JS -->

@section('scripts')
<script>
    function handleLogin() {
        const email = document.getElementById("loginEmail").value;
        const password = document.getElementById("loginPass").value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken || ""
                },
                body: JSON.stringify({
                    email,
                    password
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log("Backend Response:", data);

                // ✅ 1. Match your backend key: 'token'
                if (data.token) {
                    localStorage.setItem("token", data.token);

                    // ✅ 2. Match your backend key: 'role' (Not data.user.role)
                    const role = data.role;
                    const status = data.approval_status;
                    localStorage.setItem("role", role || "");
                    
                    // Check for redirect URL from search page
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirectUrl = urlParams.get('redirect');
                    const intendedUrl = data.intended_url;
                    
                    // Check if there's a pending professional ID to view
                    const pendingProId = localStorage.getItem('pending_pro_id');
                    
                    // ✅ 3. Redirect Logic
                    // IMPORTANT: Use replace to avoid login page in browser history
                    let redirectTarget;
                    if (intendedUrl) {
                        redirectTarget = intendedUrl;
                    } else if (role === 'admin') {
                        redirectTarget = "/admin/dashboard";
                    } else if (role === 'professional') {
                        if (status === 'pending') {
                            alert("Your account is still under review by Admin.");
                        }
                        redirectTarget = "/pro/dashboard";
                    } else if (role === 'client') {
                        if (redirectUrl) {
                            redirectTarget = redirectUrl;
                        } else {
                            redirectTarget = "/client/dashboard";
                        }
                    } else {
                        redirectTarget = "/";
                    }
                    
                    // Replace history to prevent login page from being in back button stack
                    window.history.replaceState(null, '', redirectTarget);
                    window.location.href = redirectTarget;
                } else {
                    // This handles the "Invalid credentials" or "Suspended" message
                    alert(data.message || "Login Failed");
                }
            })
            .catch(err => {
                console.error("Critical Error:", err);
                alert("Server connection failed.");
            });

    }

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
