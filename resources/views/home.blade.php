@extends('layouts.app')

@section('content')



<!-- Banner -->
<section class="hero-section d-flex align-items-center background-overlay" style="background-image: url('{{ asset('images/banner.jpg') }}');">
    <div class="container text-center text-white hero-content-container">
        <h1 class="fw-bold mb-3 display-4 hero-headline">
            Find Verified Local Experts, Fast.
        </h1>
        <p class="mb-5 lead opacity-90">
            Post your job in minutes. Connect with trusted professionals in seconds.
        </p>

        <div class="hero-search-wrapper mx-auto">
            <div class="card p-4 shadow-lg hero-search-card border-0 rounded-4">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-secondary"><i class="fa-solid fa-location-dot"></i></span>
<input id="location" class="form-control form-control-lg border-0 ps-1" placeholder="City or Area (e.g., Addis)">                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-secondary"><i class="fa-solid fa-briefcase"></i></span>
<input id="service" class="form-control form-control-lg border-0 ps-1" placeholder="What do you need? (e.g., Electrician)">                        </div>
                    </div>
                    <div class="col-md-3">
                        <button onclick="searchExperts()" class="btn btn-success btn-sm w-100 rounded-pill fw-bold h-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-search"></i> Search Experts
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- how work -->
<section id="services" class="py-5 bg-light">
    <div class="container text-center">

        <h2 class="fw-bold mb-5">How It Works</h2>

        <div class="row g-4">

            <!-- CARD 1 -->
            <div class="col-md-4">
                <div class="work-card p-4 shadow-sm h-100">
                    <div class="icon mb-3">📤</div>
                    <h5 class="fw-bold">1. Post Your Job</h5>
                    <p class="text-muted">Tell us what you need.</p>
                </div>
            </div>

            <!-- CARD 2 -->
            <div class="col-md-4">
                <div class="work-card p-4 shadow-sm h-100">
                    <div class="icon mb-3">🤝</div>
                    <h5 class="fw-bold">2. Receive Offers</h5>
                    <p class="text-muted">Verified professionals apply to you.</p>
                </div>
            </div>

            <!-- CARD 3 -->
            <div class="col-md-4">
                <div class="work-card p-4 shadow-sm h-100">
                    <div class="icon mb-3">⭐</div>
                    <h5 class="fw-bold">3. Pick the Best</h5>
                    <p class="text-muted">Compare and hire confidently.</p>
                </div>
            </div>

        </div>

    </div>
</section>


<!-- testimonial section -->
<section class="py-5">
    <div class="container text-center">

        <h2 class="fw-bold mb-5">What Our Clients Say</h2>

        <div class="row g-4">

            <!-- TESTIMONIAL 1 -->
            <div class="col-md-4">
                <div class="testimonial-card p-4 shadow-sm h-100">
                    <img src="/images/user1.jpg" class="rounded-circle mb-3" width="80" height="80">
                    
                    <h6 class="fw-bold">Abel Tesfaye</h6>

                    <!-- STARS -->
                <div class="text-warning mb-2">
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
</div>

                    <p class="text-muted">
                        Found a professional in minutes. The process was smooth and fast.
                    </p>
                </div>
            </div>

            <!-- TESTIMONIAL 2 -->
            <div class="col-md-4">
                <div class="testimonial-card p-4 shadow-sm h-100">
                    <img src="/images/user2.jpg" class="rounded-circle mb-3" width="80" height="80">

                    <h6 class="fw-bold">Sara Mekonnen</h6>

                 <div class="text-warning mb-2">
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
</div>

                    <p class="text-muted">
                        Very reliable platform. I hired a designer and the result was amazing.
                    </p>
                </div>
            </div>

            <!-- TESTIMONIAL 3 -->
            <div class="col-md-4">
                <div class="testimonial-card p-4 shadow-sm h-100">
                    <img src="/images/user3.jpg" class="rounded-circle mb-3" width="80" height="80">

                    <h6 class="fw-bold">Daniel Girma</h6>
<div class="text-warning mb-2">
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
    <i class="fas fa-star"></i>
</div>

                    <p class="text-muted">
                        EasyHire saved my time. Great professionals and fast communication.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- Subscription Plans Section -->
<section class="py-5" style="background: linear-gradient(135deg, #1e4a42, #2d665b);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2 text-white">Our Subscription Plans</h2>
            <p class="opacity-75" style="color: rgba(220, 244, 230, 0.7);">Choose the perfect plan for your hiring needs</p>
        </div>

        <div id="home-plans-container" class="row g-2 justify-content-center">
            <div class="col-12 text-center text-white-50 py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading plans...
            </div>
        </div>

        <div class="text-center mt-4" id="home-get-started-btn" style="display: none;">
            <a href="/register?role=client" class="btn btn-light btn-lg rounded-pill px-4">
                Get Started as a Client
            </a>
        </div>
    </div>
</section>


 <!-- Professional Trust Badges -->
<section class="py-4 bg-white  border-bottom">
<div class="container my-5">
    <div class="row justify-content-center border-top pt-5">
        <div class="col-md-8">
            <div class="d-flex justify-content-around align-items-center">
                
                <!-- Chapa Badge -->
                <div class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                            <i class="fas fa-lock text-white fs-5"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-white rounded-circle d-flex align-items-center justify-content-center border" style="width: 20px; height: 20px;">
                            <i class="fas fa-check text-success" style="font-size: 10px;"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Chapa Secure</h6>
                        <small class="text-muted">Encrypted Payments</small>
                    </div>
                </div>

                <!-- ID Verified Badge -->
                <div class="d-flex align-items-center border-start ps-5">
                    <div class="position-relative me-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; background-color: #0d6efd !important;">
                            <i class="fas fa-user-check text-white fs-5"></i>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-white rounded-circle d-flex align-items-center justify-content-center border" style="width: 20px; height: 20px;">
                            <i class="fas fa-check text-primary" style="font-size: 10px;"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">ID Verified</h6>
                        <small class="text-muted">Pro Professionals</small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</section>

@endsection


@section('scripts')

<script>
function searchExperts() {
    const location = document.getElementById("location")?.value || "";
    const service = document.getElementById("service")?.value || "";

    if (!location && !service) {
        alert("Enter location or service");
        return;
    }

    window.location.href = `/search?location=${location}&service=${service}`;
}

async function loadHomePlans() {
    const container = document.getElementById('home-plans-container');
    if (!container) return;

    try {
        const response = await fetch('/api/plans');
        const plans = await response.json();
        
        if (!plans || plans.length === 0) {
            container.innerHTML = '<div class="col-12 text-center text-muted py-4">No plans available at the moment.</div>';
            return;
        }

        container.innerHTML = plans.map((plan, index) => {
            const isPopular = index === 1;
            const bgStyle = isPopular 
                ? 'background: rgba(138, 219, 174, 0.20); box-shadow: 0 8px 22px rgba(7, 19, 16, 0.14);'
                : 'background: rgba(255, 255, 255, 0.12);';
            const planColor = isPopular ? '#effff4' : '#c8f0d6';
            const popularBadge = isPopular 
                ? '<span class="small fw-bold px-2 py-1 rounded-pill" style="background: #e5f8ec; color: #1b4037;">Popular</span>'
                : '';

            return `
                <div class="col-12 col-md-4">
                    <div class="card border-0 rounded-4 h-100" style="${bgStyle}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-uppercase mb-0" style="color: ${planColor}; letter-spacing: 0.08em;">${plan.name || 'Plan'}</h6>
                                ${popularBadge}
                            </div>
                            <h3 class="fw-bold text-white mb-0">Br${plan.price || 0}</h3>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${plan.job_posts_limit || 0} Job Posts</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${plan.duration_days || 0} Days</p>
                                <button class="btn btn-light btn-sm rounded-pill fw-semibold px-3 py-1" onclick="homeBuyPlan(${plan.id})">Buy Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">Failed to load plans. Please refresh.</div>';
    }
}

function homeBuyPlan(planId) {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login?redirect=/';
        return;
    }

    fetch('/api/buy-plan/' + planId, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        } else if (data.message) {
            alert(data.message);
        }
    })
    .catch(err => {
        alert('Failed to purchase plan. Please try again.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Hide "Get Started" button if logged in
    const token = localStorage.getItem('token');
    if (token) {
        const btn = document.getElementById('home-get-started-btn');
        if (btn) btn.style.display = 'none';
    }
    
    loadHomePlans();
});
</script>

@endsection

