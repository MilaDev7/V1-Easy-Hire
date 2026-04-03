@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow-sm border-0 rounded-4">
                <h4 class="fw-bold mb-4 text-center">Create Account</h4>

                <!-- 1. Full Name -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input id="regName" class="form-control" placeholder="Jone Doe">
                </div>

                <!-- 2. Email -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input id="regEmail" type="email" class="form-control" placeholder="cli@test.com">
                </div>

                <!-- 3. Passwords -->
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input id="regPassword" type="password" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Confirm</label>
                        <input id="regConfirm" type="password" class="form-control">
                    </div>
                </div>

                <!-- 4. Role Selection -->
                <div class="mb-4">
                    <label class="form-label small fw-bold">Register as:</label>
                    <select id="regRole" class="form-select border-primary">
                        <option value="client">Client (Hiring)</option>
                        <option value="professional">Professional (Working)</option>
                    </select>
                </div>

                <!-- ERROR BOX -->
                <div id="errorBox" class="text-danger small mb-3"></div>

                <!-- SUBMIT BUTTON -->
                <button type="button" onclick="executeRegister()" id="submitBtn" class="btn btn-primary w-100 py-2 fw-bold">
                    Create Account
                </button>

                <p class="mt-3 text-center small text-muted">
                    Already have an account? <a href="/login" class="text-primary fw-bold">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function executeRegister() {

        const name = document.getElementById("regName").value;
        const email = document.getElementById("regEmail").value;
        const password = document.getElementById("regPassword").value;
        const confirm = document.getElementById("regConfirm").value;
        const role = document.getElementById("regRole").value;
        const btn = document.getElementById("submitBtn");
        const errorBox = document.getElementById("errorBox");

        errorBox.innerHTML = "";
        btn.disabled = true;
        btn.innerHTML = "Processing...";

        if (password !== confirm) {
            errorBox.innerHTML = "Passwords do not match!";
            btn.disabled = false;
            btn.innerHTML = "Create Account";
            return;
        }

        fetch(`/api/register/${role}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: confirm
                })
            })
            .then(async res => {
                const data = await res.json();

                if (!res.ok) throw data;

                // ✅ Save token
                localStorage.setItem("token", data.access_token);

                alert("Account Created Successfully!");

                // ✅ CORRECT REDIRECT
                if (role === "professional") {
                    window.location.href = "/professional-setup";
                } else {
                    window.location.href = "/client-setup";
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = "Create Account";

                if (err.errors) {
                    errorBox.innerHTML = Object.values(err.errors).flat().join("<br>");
                } else {
                    errorBox.innerHTML = err.message || "Something went wrong";
                }
            });
    }
</script>
@endsection