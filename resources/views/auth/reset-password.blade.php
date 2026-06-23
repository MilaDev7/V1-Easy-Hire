@extends('layouts.app')

@section('content')
<style>
  .auth-page {
    min-height: calc(100vh - 76px);
    display: flex;
    align-items: center;
    padding: 2rem 1rem;
  }
  .auth-card {
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
    width: 56px; height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #198754, #145c32);
    font-size: 26px; color: #fff;
  }
  .form-control-lg-custom {
    height: 48px; font-size: 0.95rem;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .form-control-lg-custom:focus {
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.12);
  }
  .form-label-custom {
    font-size: 0.85rem; font-weight: 600;
    color: #1e293b; margin-bottom: 0.35rem;
  }
  .btn-auth {
    height: 48px; border-radius: 12px;
    font-weight: 600; font-size: 0.95rem;
    background: #0f172a; border: none;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-auth:hover {
    background: #1e293b; transform: translateY(-1px);
  }
  .btn-auth:active { transform: translateY(0); }
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

<div class="auth-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-6 col-lg-4">
        <div class="auth-card shadow-lg p-4 p-sm-5">
          <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
              <i class="fa-solid fa-key"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Reset Password</h4>
            <p class="text-muted small mb-0">Choose a new password for your account.</p>
          </div>

          @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('password.reset.submit') }}" novalidate>
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
              <label for="password" class="form-label-custom">New Password</label>
              <div class="position-relative">
                <input type="password" name="password" id="password"
                       class="form-control form-control-lg-custom pe-5 @error('password') is-invalid @enderror"
                       placeholder="Min. 8 characters" required minlength="8"
                       oninput="checkResetPasswordStrength(this.value)"
                       data-testid="new-password-input">
                <button type="button" class="eye-btn position-absolute" onclick="togglePassword('password', this)">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="password-strength mt-2">
                <div class="progress" style="height: 4px;">
                  <div id="resetStrengthBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex flex-wrap gap-2 small mt-2 mb-0" id="resetPasswordRequirements">
                  <span id="reset-req-length" class="text-muted"><i class="fa-regular fa-circle me-1"></i> 8+ chars</span>
                  <span id="reset-req-uppercase" class="text-muted"><i class="fa-regular fa-circle me-1"></i> Uppercase</span>
                  <span id="reset-req-lowercase" class="text-muted"><i class="fa-regular fa-circle me-1"></i> Lowercase</span>
                  <span id="reset-req-number" class="text-muted"><i class="fa-regular fa-circle me-1"></i> Number</span>
                  <span id="reset-req-special" class="text-muted"><i class="fa-regular fa-circle me-1"></i> Special char</span>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label-custom">Confirm Password</label>
              <div class="position-relative">
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control form-control-lg-custom pe-5"
                       placeholder="Repeat new password" required minlength="8"
                       data-testid="confirm-password-input">
                <button type="button" class="eye-btn position-absolute" onclick="togglePassword('password_confirmation', this)">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-auth w-100 text-white" data-testid="reset-password-btn">
              Reset Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
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

    function checkPasswordStrength(password) {
        return {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password),
        };
    }

    function updateStrengthUI(prefix, checks) {
        const passed = Object.values(checks).filter(Boolean).length;
        const pct = (passed / 5) * 100;
        const bar = document.getElementById(prefix + 'StrengthBar');
        if (bar) {
            bar.style.width = pct + '%';
            bar.className = 'progress-bar';
            if (pct === 100) bar.classList.add('bg-success');
            else if (pct >= 60) bar.classList.add('bg-warning');
            else bar.classList.add('bg-danger');
        }
        for (const [key, met] of Object.entries(checks)) {
            const el = document.getElementById(prefix + 'req-' + key);
            if (el) {
                el.className = met ? 'text-success' : 'text-muted';
                const icon = el.querySelector('i');
                if (icon) {
                    icon.className = met ? 'fa-regular fa-circle-check me-1' : 'fa-regular fa-circle me-1';
                }
            }
        }
    }

    function checkResetPasswordStrength(value) {
        updateStrengthUI('reset-', checkPasswordStrength(value));
    }
</script>
@endsection
