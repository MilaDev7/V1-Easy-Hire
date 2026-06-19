@extends('layouts.app')

@section('content')
<style>
  .setup-page {
    min-height: calc(100vh - 76px);
    display: flex;
    align-items: center;
    padding: 2rem 1rem;
  }
  .setup-card {
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
  .btn-primary-custom {
    height: 48px; border-radius: 12px;
    font-weight: 600; font-size: 0.95rem;
    background: #0f172a; border: none;
    transition: background 0.2s, transform 0.15s;
  }
  .btn-primary-custom:hover {
    background: #1e293b; transform: translateY(-1px);
  }
</style>

<div class="setup-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-9 col-md-6 col-lg-5">
        <div class="setup-card shadow-lg p-4 p-sm-5 text-center">
          <div class="brand-icon mx-auto mb-3">
            <i class="fa-solid fa-camera"></i>
          </div>
          <h4 class="fw-bold mb-1" style="color: #0f172a;">Welcome, Client!</h4>
          <p class="text-muted small mb-4">Add a profile photo to build trust with professionals.</p>

          <div class="mb-4">
            <input type="file" id="clientPhoto" class="form-control form-control-lg-custom" accept="image/*">
          </div>

          <button onclick="uploadClient()" class="btn btn-primary-custom w-100 text-white">
            Finish & Start Hiring
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-body text-center py-5">
        <div class="mb-3" style="font-size: 48px; color: #198754;">
          <i class="fa-solid fa-check-circle"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #0f172a;">Profile Ready!</h5>
        <p class="text-muted small mb-0">Redirecting to your dashboard...</p>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function uploadClient() {
    const file = document.getElementById("clientPhoto").files[0];
    if (!file) { window.location.href = "/client/dashboard"; return; }

    const fd = new FormData();
    fd.append("profile_photo", file);

    const btn = document.querySelector('.btn-primary-custom');
    btn.disabled = true;
    btn.textContent = 'Uploading...';

    fetch("/api/client/update-photo", {
        method: "POST",
        headers: { "Authorization": "Bearer " + localStorage.getItem("token"), "Accept": "application/json" },
        body: fd
    })
    .then(res => res.json())
    .then(() => {
      const modal = new bootstrap.Modal(document.getElementById('successModal'));
      modal.show();
      setTimeout(() => window.location.href = "/client/dashboard", 1500);
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Finish & Start Hiring';
    });
}
</script>
@endsection
