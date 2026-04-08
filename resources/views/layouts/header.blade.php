  <!-- navbar -->


  <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
      <div class="container">

          <!-- Logo -->
          <a class="navbar-brand fw-bold text-success" href="/">
              EasyHire
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

              @auth
                  <div class="d-flex ms-auto align-items-center gap-3">
                      <div class="d-flex align-items-center gap-2">
                          <img id="header-client-photo"
                              src="{{ Auth::user()->profile_photo ? asset('storage/' . Auth::user()->profile_photo) : asset('images/user1.jpg') }}"
                              alt="Profile"
                              class="rounded-circle border"
                              style="width: 36px; height: 36px; object-fit: cover;">

                          <span id="header-client-name" class="fw-semibold text-dark">{{ Auth::user()->name }}</span>
                      </div>

                      <div class="dropdown">
                          <button class="btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-chevron-down text-muted"></i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow">
                              <li><a class="dropdown-item" href="{{ Auth::user()->role === 'admin' ? '/admin/dashboard' : (Auth::user()->role === 'professional' ? '/professional/dashboard' : '/client/dashboard') }}"><i class="fa-solid fa-user me-2"></i>Account</a></li>
                              <li><hr class="dropdown-divider"></li>
                              <li><a class="dropdown-item text-danger" href="#" onclick="logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                          </ul>
                      </div>
                  </div>
              @else
                  <!-- Guest Buttons -->
                  <div class="d-flex gap-2">
                      <a href="/register" class="btn btn-success">Register</a>
                      <a href="/login" class="btn btn-outline-success">Login</a>
                  </div>
              @endauth
          </div>

      </div>
  </nav>