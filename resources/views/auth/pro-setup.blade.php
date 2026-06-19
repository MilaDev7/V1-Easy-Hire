@extends('layouts.app')
@section('content')
<style>
  .setup-page {
    min-height: calc(100vh - 76px);
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
</style>

<div class="setup-page">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-11 col-sm-10 col-md-8 col-lg-7">
        <div class="setup-card shadow-lg p-4 p-sm-5">
          <div class="text-center mb-4">
            <div class="brand-icon mx-auto mb-3">
              <i class="fa-solid fa-user-gear"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Complete Professional Profile</h4>
            <p class="text-muted small mb-0">Fill in your details to start receiving job offers.</p>
          </div>

          {{-- Inline error --}}
          <div id="proAlert" class="alert alert-danger d-none py-2 small" role="alert"></div>

          <form id="setupForm" novalidate>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="f_photo" class="form-label-custom">Profile Photo</label>
                  <input type="file" id="f_photo" class="form-control form-control-lg-custom" accept="image/*" required>
                </div>
                <div class="mb-3">
                  <label for="f_id" class="form-label-custom">National ID Photo</label>
                  <input type="file" id="f_id" class="form-control form-control-lg-custom" accept="image/*" required>
                </div>
                <div class="mb-3">
                  <label for="f_skill" class="form-label-custom">Primary Skill</label>
                  <select id="f_skill" class="form-select form-control-lg-custom" required>
                    <option value="">Select your profession...</option>
                    <optgroup label="Construction & Technical">
                      <option value="Electrician">Electrician</option>
                      <option value="Plumber">Plumber</option>
                      <option value="Carpenter">Carpenter</option>
                      <option value="Painter">Painter</option>
                      <option value="Welder">Welder</option>
                      <option value="Elevator Technician">Elevator Technician</option>
                      <option value="Auto Mechanic">Auto Mechanic</option>
                      <option value="Construction Worker">Construction Worker</option>
                    </optgroup>
                    <optgroup label="Home Services">
                      <option value="Cleaner">Cleaner / Housekeeping</option>
                      <option value="Gardener">Gardener</option>
                      <option value="Laundry">Laundry Specialist</option>
                      <option value="Chef">Private Chef / Cook</option>
                    </optgroup>
                    <optgroup label="Digital & Creative">
                      <option value="Graphic Designer">Graphic Designer</option>
                      <option value="Web Developer">Web Developer</option>
                      <option value="Digital Marketer">Digital Marketer</option>
                    </optgroup>
                    <optgroup label="Other Professionals">
                      <option value="Tutor">Private Tutor</option>
                      <option value="Driver">Driver</option>
                      <option value="Security">Security Guard</option>
                    </optgroup>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="f_exp" class="form-label-custom">Experience (Years)</label>
                  <input type="number" id="f_exp" class="form-control form-control-lg-custom" required>
                </div>
                <div class="mb-3">
                  <label for="f_loc" class="form-label-custom">Location (City)</label>
                  <select id="f_loc" name="location" class="form-select form-control-lg-custom" required>
                    <option value="">Select city</option>
                    <option value="Addis Ababa">Addis Ababa</option>
                    <option value="Adama">Adama</option>
                    <option value="Bahir Dar">Bahir Dar</option>
                    <option value="Hawassa">Hawassa</option>
                    <option value="Mekelle">Mekelle</option>
                    <option value="Dire Dawa">Dire Dawa</option>
                    <option value="Jimma">Jimma</option>
                    <option value="Dessie">Dessie</option>
                    <option value="Gondar">Gondar</option>
                    <option value="Bishoftu">Bishoftu</option>
                    <option value="Arba Minch">Arba Minch</option>
                    <option value="Harar">Harar</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="f_bio" class="form-label-custom">Bio</label>
                  <textarea id="f_bio" class="form-control form-control-lg-custom" rows="3" required></textarea>
                </div>
                <div class="row">
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="f_age" class="form-label-custom">Age</label>
                      <input type="number" id="f_age" class="form-control form-control-lg-custom">
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="f_gender" class="form-label-custom">Gender</label>
                      <select id="f_gender" class="form-select form-control-lg-custom">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="f_cv" class="form-label-custom">CV (PDF Only)</label>
                  <input type="file" id="f_cv" class="form-control form-control-lg-custom" accept=".pdf" required>
                </div>
                <div class="mb-3">
                  <label for="f_cert" class="form-label-custom">Certificate (PDF/Image)</label>
                  <input type="file" id="f_cert" class="form-control form-control-lg-custom" accept=".pdf,image/*">
                </div>
              </div>
            </div>
            <button type="button" onclick="completePro()" id="submitBtn" class="btn btn-auth w-100 text-white">
              Finish & Submit for Review
            </button>
          </form>
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
        <h5 class="fw-bold mb-1" style="color: #0f172a;">Profile Submitted!</h5>
        <p class="text-muted small mb-0">Admin is reviewing your profile.</p>
      </div>
    </div>
  </div>
</div>

{{-- Error Modal --}}
<div class="modal fade" id="errorModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-body text-center py-5">
        <div class="mb-3" style="font-size: 48px; color: #dc3545;">
          <i class="fa-solid fa-times-circle"></i>
        </div>
        <h5 class="fw-bold mb-1" style="color: #0f172a;">Error</h5>
        <p class="text-muted small mb-3" id="errorModalMessage">Something went wrong.</p>
        <button class="btn btn-auth w-100 text-white" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    function showProError(message) {
        const alert = document.getElementById('proAlert');
        alert.textContent = message;
        alert.classList.remove('d-none');
    }

    function hideProAlert() {
        document.getElementById('proAlert').classList.add('d-none');
    }

    function completePro() {
        hideProAlert();

        const fd = new FormData();
        const skill = document.getElementById("f_skill").value;
        const exp = document.getElementById("f_exp").value;
        const bio = document.getElementById("f_bio").value;
        const location = document.getElementById("f_loc").value;
        const photo = document.getElementById("f_photo").files[0];
        const idCard = document.getElementById("f_id").files[0];
        const cv = document.getElementById("f_cv").files[0];
        const btn = document.getElementById("submitBtn");

        if (!skill || !exp || !bio || !location || !photo || !idCard || !cv) {
            showProError("Please fill all required fields and upload all required documents.");
            return;
        }

        if (bio.length < 20) {
            showProError("Bio must be at least 20 characters.");
            return;
        }

        fd.append("skill", skill);
        fd.append("experience", exp);
        fd.append("bio", bio);
        fd.append("age", document.getElementById("f_age").value);
        fd.append("gender", document.getElementById("f_gender").value);
        fd.append("location", location);
        fd.append("profile_photo", photo);
        fd.append("id_card", idCard);
        fd.append("cv", cv);

        if (document.getElementById("f_cert").files[0]) {
            fd.append("certificate", document.getElementById("f_cert").files[0]);
        }

        btn.disabled = true;
        btn.textContent = 'Submitting...';

        fetch("/api/pro/complete-profile", {
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                    "Accept": "application/json"
                },
                body: fd
            })
            .then(async res => {
                const data = await res.json();

                if (!res.ok) {
                    btn.disabled = false;
                    btn.textContent = 'Finish & Submit for Review';
                    const msg = data.errors
                        ? Object.values(data.errors).flat().join("\n")
                        : (data.message || "Something failed.");
                    document.getElementById('errorModalMessage').textContent = msg;
                    new bootstrap.Modal(document.getElementById('errorModal')).show();
                    return;
                }

                localStorage.setItem("approval_status", "pending");
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
                setTimeout(() => window.location.href = "/pro/dashboard", 2000);
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Finish & Submit for Review';
                document.getElementById('errorModalMessage').textContent = 'Server connection failed. Please try again.';
                new bootstrap.Modal(document.getElementById('errorModal')).show();
            });
    }
</script>
@endsection
