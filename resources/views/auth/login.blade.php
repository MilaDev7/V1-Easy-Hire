@extends('layouts.app')

@section('content')
<style>
  .login-page {
    min-height: calc(100vh - 76px);
    display: flex;
    align-items: center;
    padding: 2rem 1rem;
  }

  .login-card {
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

  .btn-login {
    height: 48px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    background: #0f172a;
    border: none;
    transition: background 0.2s, transform 0.15s;
  }

  .btn-login:hover {
    background: #1e293b;
    transform: translateY(-1px);
  }

  .btn-login:active {
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

<div class="login-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-6 col-lg-4">

        <div class="login-card shadow-lg p-4 p-sm-5">
          {{-- Brand --}}
          <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
              <i class="fa-solid fa-briefcase"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Welcome Back</h4>
            <p class="text-muted small mb-0">Sign in to your EasyHire account</p>
          </div>

          {{-- Inline error alerts --}}
          <div id="loginAlert" class="alert d-none py-2 small" role="alert"></div>

          {{-- Form --}}
          <div class="mb-3">
            <label for="loginEmail" class="form-label-custom">Email Address</label>
            <input type="email" id="loginEmail" class="form-control form-control-lg-custom" placeholder="name@example.com">
            <div id="emailError" class="invalid-feedback"></div>
          </div>

          <div class="mb-3">
            <label for="loginPass" class="form-label-custom">Password</label>
            <div class="position-relative">
              <input type="password" id="loginPass" class="form-control form-control-lg-custom pe-5" placeholder="Enter your password">
              <button type="button" class="eye-btn position-absolute" onclick="togglePassword('loginPass', this)">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
            <div id="passwordError" class="invalid-feedback"></div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe">
              <label class="form-check-label small" for="rememberMe" style="color: #475569;">Remember me</label>
            </div>
            <a href="{{ route('password.forgot.form') }}" class="text-decoration-none small fw-semibold" style="color: #198754;">Forgot Password?</a>
          </div>

          <button onclick="handleLogin()" class="btn btn-login w-100 text-white" data-testid="login-submit">
            Sign In
          </button>

          <p class="text-center text-muted small mt-4 mb-0">
            Don't have an account?
            <a href="/register" class="fw-semibold text-decoration-none" style="color: #198754;">Register</a>
          </p>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script>
    function showLoginError(message, type) {
        const alert = document.getElementById('loginAlert');
        alert.className = 'alert alert-' + (type || 'danger') + ' py-2 small';
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function hideLoginError() {
        const alert = document.getElementById('loginAlert');
        alert.classList.add('d-none');
    }

    function setFieldValidity(id, isValid) {
        const input = document.getElementById(id);
        input.classList.toggle('is-invalid', !isValid);
    }

    function handleLogin() {
        const email = document.getElementById("loginEmail").value.trim();
        const password = document.getElementById("loginPass").value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const btn = document.querySelector('[data-testid="login-submit"]');

        hideLoginError();
        setFieldValidity('loginEmail', true);
        setFieldValidity('loginPass', true);

        if (!email) {
            showLoginError('Please enter a valid email address.');
            setFieldValidity('loginEmail', false);
            document.getElementById('loginEmail').focus();
            return;
        }

        if (!password) {
            showLoginError('Please enter your password.');
            setFieldValidity('loginPass', false);
            document.getElementById('loginPass').focus();
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Signing in...';

        fetch("/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken || ""
                },
                body: JSON.stringify({ email, password })
            })
            .then(async res => {
                const data = await res.json();
                btn.disabled = false;
                btn.textContent = 'Sign In';

                if (data.token) {
                    localStorage.setItem("token", data.token);

                    const role = data.role;
                    const status = data.approval_status;
                    localStorage.setItem("role", role || "");

                    const urlParams = new URLSearchParams(window.location.search);
                    const redirectUrl = urlParams.get('redirect');
                    const intendedUrl = data.intended_url;
                    const needsSetup = data.needs_setup;

                    let redirectTarget;
                    if (intendedUrl) {
                        redirectTarget = intendedUrl;
                    } else if (needsSetup && role === 'client') {
                        redirectTarget = "/client-setup";
                    } else if (needsSetup && role === 'professional') {
                        redirectTarget = "/professional-setup";
                    } else if (role === 'admin') {
                        redirectTarget = "/admin/dashboard";
                    } else if (role === 'professional') {
                        if (status === 'pending') {
                            showLoginError('Your account is still under review by Admin.', 'warning');
                            return;
                        }
                        redirectTarget = "/pro/dashboard";
                    } else if (role === 'client') {
                        redirectTarget = redirectUrl || "/client/dashboard";
                    } else {
                        redirectTarget = "/";
                    }

                    window.history.replaceState(null, '', redirectTarget);
                    window.location.href = redirectTarget;
                } else if (res.status === 429) {
                    showLoginError('Too many login attempts. Please try again later.');
                } else if (res.status === 403) {
                    showLoginError('Your account has been suspended.');
                } else {
                    showLoginError('Invalid email or password.');
                    setFieldValidity('loginEmail', false);
                    setFieldValidity('loginPass', false);
                }

                return null;
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'Sign In';
                console.error("Login error:", err);
                showLoginError('Something went wrong. Please try again.');
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
