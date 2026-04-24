  <!-- navbar -->
  <style>
      body.site-dark-mode {
          background: #111827 !important;
          color: #f9fafb;
      }

      body.site-dark-mode .nav-link {
          color: #e5e7eb !important;
      }

      body.site-dark-mode .nav-link:hover {
          color: #ffffff !important;
      }
  </style>


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
  function isSiteDarkModeEnabled() {
      return localStorage.getItem('site_theme') === 'dark';
  }

  function applySiteTheme() {
      const isDark = isSiteDarkModeEnabled();
      const nav = document.querySelector('nav.navbar');

      document.body.classList.toggle('site-dark-mode', isDark);

      if (nav) {
          nav.classList.toggle('bg-white', !isDark);
          nav.classList.toggle('navbar-light', !isDark);
          nav.classList.toggle('bg-dark', isDark);
          nav.classList.toggle('navbar-dark', isDark);
      }
  }

  function toggleSiteDarkMode() {
      if (isSiteDarkModeEnabled()) {
          localStorage.removeItem('site_theme');
      } else {
          localStorage.setItem('site_theme', 'dark');
      }

      applySiteTheme();
      renderAuthSection();
  }

  // Render auth section based on user state
  async function renderAuthSection() {
      const authSection = document.getElementById('auth-section');
      if (!authSection) return;

      const user = await window.initAuth();
      const isDark = isSiteDarkModeEnabled();

      if (user) {
          // Logged in - show profile photo + name + dropdown
          const dashboardUrl = user.role === 'admin' 
              ? '/admin/dashboard' 
              : (user.role === 'professional' ? '/pro/dashboard' : '/client/dashboard');
          const defaultPhoto = @json(asset('images/user1.jpg'));
          const normalizePhotoUrl = (value) => {
              if (typeof value !== 'string') return defaultPhoto;
              const trimmed = value.trim();
              if (!trimmed) return defaultPhoto;
              if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('/')) {
                  return trimmed;
              }
              return `/storage/${trimmed.replace(/^storage\//, '')}`;
          };
          const photoUrl = normalizePhotoUrl(user.profile_photo);

          authSection.innerHTML = `
              <div class="d-flex ms-auto align-items-center gap-2">
                  <span class="fw-semibold ${isDark ? 'text-light' : 'text-dark'}">${user.name}</span>
                  <div class="dropdown">
                      <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                          <img
                              src="${photoUrl}"
                              alt="Profile"
                              class="rounded-circle border"
                              style="width: 36px; height: 36px; object-fit: cover;"
                              onerror="this.onerror=null;this.src='${defaultPhoto}';"
                          >
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow">
                          <li><a class="dropdown-item" href="${dashboardUrl}"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                          <li><button type="button" class="dropdown-item" onclick="toggleSiteDarkMode()"><i class="fa-solid fa-moon me-2"></i>${isDark ? 'Light Mode' : 'Dark Mode'}</button></li>
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
  document.addEventListener('DOMContentLoaded', function () {
      applySiteTheme();
      renderAuthSection();
  });

  // Keep header in sync after back/forward restores and auth changes.
  window.addEventListener('pageshow', function () {
      renderAuthSection();
  });

  window.addEventListener('auth:changed', function () {
      renderAuthSection();
  });
  </script>
