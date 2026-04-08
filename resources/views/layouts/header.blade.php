  <!-- navbar -->


  <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
      <div class="container">

          <!-- Logo -->
          <a class="navbar-brand d-flex align-items-center gap-2" href="/">
              <div style="background: linear-gradient(135deg, #198754, #20c997); width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);">
                  <i class="fa-solid fa-bolt text-white fs-5"></i>
              </div>
              <span class="fw-bold" style="background: linear-gradient(135deg, #198754, #20c997); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">EasyHire</span>
          </a>

          <!-- Toggle (mobile) -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
              <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Links -->
          <div class="collapse navbar-collapse" id="nav">
              <ul class="navbar-nav mx-auto">
                  <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                  <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                  <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                  <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
              </ul>

              <!-- Auth Section - rendered by JS -->
              <div id="auth-section" class="d-flex gap-2">
                  <!-- Loading state -->
                  <div class="spinner-border spinner-border-sm text-success" role="status">
                      <span class="visually-hidden">Loading...</span>
                  </div>
              </div>
          </div>

      </div>
  </nav>

  <script>
  // Render auth section based on user state
  async function renderAuthSection() {
      const authSection = document.getElementById('auth-section');
      if (!authSection) return;

      const user = await initAuth();

      if (user) {
          // Logged in - show profile photo + name + dropdown
          const dashboardUrl = user.role === 'admin' 
              ? '/admin/dashboard' 
              : (user.role === 'professional' ? '/professional/dashboard' : '/client/dashboard');

          authSection.innerHTML = `
              <div class="d-flex ms-auto align-items-center gap-2">
                  <img src="${user.profile_photo}" alt="Profile" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                  <span class="fw-semibold text-dark">${user.name}</span>
                  <div class="dropdown">
                      <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="fa-solid fa-chevron-down text-muted"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow">
                          <li><a class="dropdown-item" href="${dashboardUrl}"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                      </ul>
                  </div>
              </div>
          `;
      } else {
          // Not logged in - show Register/Login buttons
          authSection.innerHTML = `
              <div class="d-flex gap-2">
                  <a href="/register" class="btn btn-success">Register</a>
                  <a href="/login" class="btn btn-outline-success">Login</a>
              </div>
          `;
      }
  }

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', renderAuthSection);
  </script>