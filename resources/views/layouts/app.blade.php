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

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/headerFooter.css') }}">
<!-- <style>
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
.result-card {
border-radius: 15px;
transition: 0.3s;
overflow: hidden;
}

.result-card:hover {
transform: translateY(-8px);
box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.blur-img {
filter: blur(4px);
height: 200px;
object-fit: cover;
width: 100%;
}

.skill-badge {
background: #e9f7ef;
color: #198754;
padding: 5px 10px;
border-radius: 20px;
font-size: 12px;
}
</style> -->
</head>
<body>

 @include('layouts.header')

    <main>
        @yield('content')
    </main>

@include('layouts.footer')


<!-- Bootstrap JS  -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your global API -->
<script src="{{ asset('js/api.js') }}"></script>
<script src="{{ asset('js/auth.js') }}"></script>
<script src="{{ asset('js/direct-request.js') }}"></script>

<!-- Page-specific scripts -->
@yield('scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem("token");
    const role = localStorage.getItem("role");
    const guestSection = document.getElementById("guestSection");
    const userSection = document.getElementById("userSection");
    const headerClientName = document.getElementById("header-client-name");
    const headerClientPhoto = document.getElementById("header-client-photo");
    const headerAccountLink = document.getElementById("header-account-link");

    function showGuestState() {
        if(guestSection) guestSection.classList.remove("d-none");
        if(userSection) userSection.classList.add("d-none");
    }

    if (!token || (role && role !== "client")) {
        showGuestState();
        return;
    }

    fetch("/api/client/me", {
        headers: {
            "Accept": "application/json",
            "Authorization": "Bearer " + token
        }
    })
    .then(async function (response) {
        if (!response.ok) {
            throw new Error("Unable to load client");
        }

        return response.json();
    })
    .then(function (client) {
        if(guestSection) guestSection.classList.add("d-none");
        if(userSection) userSection.classList.remove("d-none");

        if (headerClientName) {
            headerClientName.textContent = client.name || "Client";
        }

        if (headerClientPhoto && client.profile_photo) {
            headerClientPhoto.src = client.profile_photo;
        }

        if (headerAccountLink) {
            headerAccountLink.href = "/client/dashboard";
        }
    })
    .catch(function () {
        showGuestState();
    });
});

</script>
</body>
</html>
