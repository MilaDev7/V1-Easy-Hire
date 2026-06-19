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
  .otp-input {
    letter-spacing: 8px;
    font-weight: 700;
    font-size: 1.3rem;
    text-align: center;
  }
</style>

<div class="auth-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-6 col-lg-4">
        <div class="auth-card shadow-lg p-4 p-sm-5">
          <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Verify OTP</h4>
            <p class="text-muted small mb-0">
              Enter the code sent to <strong>{{ $email }}</strong>
            </p>
          </div>

          @if (session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
          @endif

          @if ($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('password.verify.otp') }}" novalidate>
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="mb-3">
              <label for="otp" class="form-label-custom">OTP Code</label>
              <input type="text" name="otp" id="otp"
                     class="form-control form-control-lg-custom otp-input @error('otp') is-invalid @enderror"
                     placeholder="000000" maxlength="6" inputmode="numeric" pattern="\d{6}" required
                     data-testid="otp-input">
              @error('otp')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-auth w-100 text-white" data-testid="verify-otp-btn">
              Verify OTP
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="{{ route('password.forgot.form') }}" class="text-decoration-none small fw-semibold" style="color: #198754;">
              Resend OTP
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
