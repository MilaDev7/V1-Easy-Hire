<!DOCTYPE html>
<html>
<head>
    <title>EasyHire</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- social meadia icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">


    <style>
    body {
        font-family: 'Inter', sans-serif;
    }

    
.nav-link {
    position: relative;
    transition: 0.3s;
}

.nav-link:hover {
    color: #198754 !important;
}

.nav-link::after {
    content: "";
    position: absolute;
    width: 0%;
    height: 2px;
    background: #198754;
    left: 0;
    bottom: -4px;
    transition: 0.3s;
}

.nav-link:hover::after {
    width: 100%;
}
/* 1. Base Hero Container */
    .hero-section {
        min-height: 85vh; /* Standard modern height */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        /* Replace image path if needed for raw HTML testing */
    }

    /* 2. The Black/White Radial Gradient Overlay (Critical for Legibility) */
    .background-overlay::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* Blend of black (for text pop) and transparent white (for the professional face) */
        background: radial-gradient(circle at 10% 10%, rgba(0, 0, 0, 0.9) 0%, rgba(255, 255, 255, 0.4) 100%);
        z-index: 1; /* Sets overlay below the text content */
    }

    /* 3. Positioning the Text Content (Above Overlay) */
    .hero-content-container {
        position: relative;
        z-index: 2; /* Content must be above the gradient overlay */
        padding-top: 10vh; /* Centers the content block */
        padding-bottom: 5vh;
    }

    /* 4. Text Hierarchy & Styling */
    .hero-headline {
        letter-spacing: -1.5px; /* Clean, modern spacing */
        line-height: 1.1;
    }

    /* 5. The Search Card (Dashboard Style) */
    .hero-search-wrapper {
        max-width: 900px; /* Constrains the search card on wide screens */
    }
    .hero-search-card {
        background-color: #ffffff;
        backdrop-filter: blur(5px); /* Adds a hint of 'Glassmorphism' */
    }

    /* 6. Professional Input Styling */
    .hero-search-card .form-control {
        background: transparent;
        font-weight: 500;
        letter-spacing: -0.2px;
    }
    .hero-search-card .form-control:focus {
        box-shadow: none; /* Disables standard Bootstrap focus glow */
    }
    .hero-search-card .input-group-text {
        font-size: 1.2rem;
    }


.work-card {
    border-radius: 15px;
    transition: all 0.3s ease;
    background: #fff;
}

.work-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.icon {
    font-size: 40px;
}

.testimonial-card {
    border-radius: 15px;
    background: #fff;
    transition: 0.3s;
}

.testimonial-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

footer a:hover { color: #198754 !important; transition: 0.3s; }
</style>
</head>
<body>
 
 
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

            <!-- Auth Buttons -->
            <div class="d-flex gap-2">
                <a href="/register" class="btn btn-success">Register</a>
                <a href="/" class="btn btn-outline-success">Login</a>
            </div>
        </div>

    </div>
</nav>

    <main>
        @yield('content')
    </main>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row text-center text-md-start">
            <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
                <h5 class="text-uppercase mb-4 fw-bold text-success">EasyHire</h5>
                <p class="small text-secondary">
                    Connecting clients with trusted professionals quickly and safely.
                </p>
                <p class="small text-secondary mt-3">
                    <i class="fas fa-envelope me-2"></i> info@easyhire.com<br>
                    <i class="fas fa-phone me-2"></i> +251 900000000
                </p>
            </div>

            <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 fw-bold">Social Icons</h6>
                <div class="d-flex justify-content-center justify-content-md-start gap-3">
                    <a href="#" class="text-white fs-4"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white fs-4"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white fs-4"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 fw-bold">About</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white">About us</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white">Professionals</a></li>
                    <li class="mb-2"><a href="#" class="text-secondary text-decoration-none hover-white">About</a></li>
                </ul>
            </div>

            <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mt-3">
                <h6 class="text-uppercase mb-4 fw-bold">Team</h6>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-2">MILA</li>
                    <li class="mb-2">Sentayhu</li>
                    <li class="mb-2">Filimon</li>
                    <li class="mb-2">Samual</li>
                </ul>
            </div>
        </div>

        <hr class="mb-4 border-secondary opacity-25">
<div class="row">
                <div class="col-12 text-center">
                    <p class="small text-secondary mb-0">Baba 2026</p>
                </div>
            </div>
    </div>
</footer>


<!-- Bootstrap JS  -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your global API -->
<script src="{{ asset('js/api.js') }}"></script>

<!-- Page-specific scripts -->
@yield('scripts')
</body>
</html>