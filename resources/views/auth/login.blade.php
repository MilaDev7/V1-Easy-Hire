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
                <div class="mb-3">
                    <input type="password" id="loginPass" class="form-control" placeholder="Password">
                </div>
                <button onclick="handleLogin()" class="btn btn-dark w-100 py-2 fw-bold">Sign In</button>
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

        fetch("/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
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
                    
                    // Check if there's a pending professional ID to view
                    const pendingProId = localStorage.getItem('pending_pro_id');
                    
                    // ✅ 3. Redirect Logic
                    if (role === 'admin') {
                        window.location.href = "/admin/dashboard";
                    } else if (role === 'professional') {
                        // If they are not approved yet, send them to a "Pending" page
                        if (status === 'pending') {
                            alert("Your account is still under review by Admin.");
                            window.location.href = "/pro/pending";
                        } else {
                            window.location.href = "/pro/dashboard";
                        }
                    } else if (role === 'client') {
                        // If there's a redirect URL (e.g., from search page), go there
                        // Otherwise go to dashboard
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            window.location.href = "/client/dashboard";
                        }
                    } else {
                        window.location.href = "/"; // Default
                    }
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
</script>
@endsection