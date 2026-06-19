@extends('layouts.app')

@section('content')
<style>
  .register-page {
    min-height: calc(100vh - 76px);
    display: flex;
    align-items: center;
    padding: 2rem 1rem;
  }

  .register-card {
    animation: fadeUp 0.6s ease-out both;
    border-radius: 20px;
    background: #fff;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .brand-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #198754, #145c32);
    font-size: 26px;
    color: #fff;
  }

  .form-control-lg-custom {
    height: 48px;
    font-size: 0.95rem;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .form-control-lg-custom:focus {
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.12);
  }

  .form-label-custom {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.35rem;
  }

  .btn-register {
    height: 48px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    background: #0f172a;
    border: none;
    transition: background 0.2s, transform 0.15s;
  }

  .btn-register:hover {
    background: #1e293b;
    transform: translateY(-1px);
  }

  .btn-register:active {
    transform: translateY(0);
  }

  .eye-btn {
    top: 50%;
    transform: translateY(-50%);
    right: 10px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 6px 10px;
    font-size: 1.1rem;
  }

  .eye-btn:hover {
    color: #475569;
  }
</style>

<div class="register-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-7 col-lg-5">

        <div class="register-card shadow-lg p-4 p-sm-5">
          {{-- Brand --}}
          <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
              <i class="fa-solid fa-user-plus"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Create Account</h4>
            <p class="text-muted small mb-0">Join EasyHire today</p>
          </div>

          {{-- Inline error/success alerts --}}
          <div id="registerAlert" class="alert d-none py-2 small" role="alert"></div>

          {{-- 1. Full Name --}}
          <div class="mb-3">
            <label for="regName" class="form-label-custom">Full Name</label>
            <input id="regName" class="form-control form-control-lg-custom" placeholder="John Doe">
            <div id="nameError" class="invalid-feedback"></div>
          </div>

          {{-- 2. Email --}}
          <div class="mb-3">
            <label for="regEmail" class="form-label-custom">Email Address</label>
            <input id="regEmail" type="email" class="form-control form-control-lg-custom" placeholder="name@example.com">
            <div id="regEmailError" class="invalid-feedback"></div>
          </div>

          {{-- 3. Phone --}}
          <div class="mb-3">
            <label for="regPhone" class="form-label-custom">Phone Number</label>
            <input id="regPhone" type="tel" class="form-control form-control-lg-custom" placeholder="09XXXXXXXX" maxlength="10" pattern="[0-9]{10}">
            <div id="phoneError" class="invalid-feedback"></div>
          </div>

          {{-- 4. Passwords --}}
          <div class="mb-3">
            <label for="regPassword" class="form-label-custom">Password</label>
            <div class="position-relative">
              <input id="regPassword" type="password" class="form-control form-control-lg-custom pe-5" placeholder="Min. 6 characters">
              <button type="button" class="eye-btn position-absolute" onclick="togglePassword('regPassword', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
            <div id="passwordError" class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <label for="regConfirm" class="form-label-custom">Confirm Password</label>
            <input id="regConfirm" type="password" class="form-control form-control-lg-custom" placeholder="Repeat password">
            <div id="confirmError" class="invalid-feedback"></div>
          </div>

          {{-- 5. Role Selection --}}
          <div class="mb-4">
            <label for="regRole" class="form-label-custom">Register as:</label>
            <select id="regRole" class="form-select form-control-lg-custom py-2">
              <option value="client">Client (Hiring)</option>
              <option value="professional">Professional (Working)</option>
            </select>
          </div>

          {{-- SUBMIT BUTTON --}}
          <button type="button" onclick="executeRegister()" id="submitBtn" class="btn btn-register w-100 text-white">
            Create Account
          </button>

          <p class="text-center text-muted small mt-4 mb-0">
            Already have an account? <a href="/login" class="fw-semibold text-decoration-none" style="color: #198754;">Login</a>
          </p>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    function showRegisterError(message) {
        const alert = document.getElementById('registerAlert');
        alert.className = 'alert alert-danger py-2 small';
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function showRegisterSuccess(message) {
        const alert = document.getElementById('registerAlert');
        alert.className = 'alert alert-success py-2 small';
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function hideRegisterAlert() {
        document.getElementById('registerAlert').classList.add('d-none');
    }

    function setFieldValidity(id, isValid) {
        const input = document.getElementById(id);
        if (input) {
            input.classList.toggle('is-invalid', !isValid);
        }
    }

    function executeRegister() {
        const name = document.getElementById("regName").value.trim();
        const email = document.getElementById("regEmail").value.trim();
        const phone = document.getElementById("regPhone").value.trim();
        const password = document.getElementById("regPassword").value;
        const confirm = document.getElementById("regConfirm").value;
        const role = document.getElementById("regRole").value;
        const btn = document.getElementById("submitBtn");

        hideRegisterAlert();
        ['regName', 'regEmail', 'regPhone', 'regPassword', 'regConfirm'].forEach(id => setFieldValidity(id, true));

        if (!name) {
            showRegisterError('Please enter your full name.');
            setFieldValidity('regName', false);
            document.getElementById('regName').focus();
            return;
        }

        if (!email) {
            showRegisterError('Please enter a valid email address.');
            setFieldValidity('regEmail', false);
            document.getElementById('regEmail').focus();
            return;
        }

        if (!/^[0-9]{10}$/.test(phone)) {
            showRegisterError('Phone number must be exactly 10 digits.');
            setFieldValidity('regPhone', false);
            document.getElementById('regPhone').focus();
            return;
        }

        if (password.length < 6) {
            showRegisterError('Password must be at least 6 characters.');
            setFieldValidity('regPassword', false);
            document.getElementById('regPassword').focus();
            return;
        }

        if (password !== confirm) {
            showRegisterError('Passwords do not match.');
            setFieldValidity('regPassword', false);
            setFieldValidity('regConfirm', false);
            document.getElementById('regPassword').focus();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = 'Creating account...';

        fetch(`/api/register/${role}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    name: name,
                    email: email,
                    phone: phone,
                    password: password,
                    password_confirmation: confirm
                })
            })
            .then(async res => {
                const data = await res.json();

                if (!res.ok) {
                    btn.disabled = false;
                    btn.innerHTML = 'Create Account';

                    if (data.errors) {
                        const firstError = Object.values(data.errors).flat()[0];
                        showRegisterError(firstError || 'Validation failed.');
                        for (const [field, messages] of Object.entries(data.errors)) {
                            const fieldMap = { name: 'regName', email: 'regEmail', phone: 'regPhone', password: 'regPassword' };
                            const inputId = fieldMap[field];
                            if (inputId) {
                                setFieldValidity(inputId, false);
                                const errorEl = document.getElementById(inputId + 'Error');
                                if (errorEl) errorEl.textContent = messages[0];
                            }
                        }
                    } else {
                        showRegisterError(data.message || 'Something went wrong.');
                    }
                    return;
                }

                // Save token
                localStorage.setItem("token", data.access_token);

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "";

                // Create web session
                const loginRes = await fetch("/login", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ email: email, password: password })
                });

                const loginData = await loginRes.json().catch(() => ({}));

                if (loginRes.ok && loginData.token) {
                    localStorage.setItem("token", loginData.token);
                }

                window.location.replace(role === "professional" ? "/professional-setup" : "/client-setup");
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Create Account';
                console.error("Registration error:", err);
                showRegisterError('Something went wrong. Please try again.');
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
