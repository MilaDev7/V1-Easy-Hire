var currentProIdForRequest = null;

function showDirectRequestModal() {
    if (!currentProIdForRequest) {
        alert('Please select a professional first');
        return;
    }
    
    var token = localStorage.getItem('token');
    if (!token) {
        alert('Please login to send a request');
        return;
    }
    
    document.getElementById('direct-request-pro-id').value = currentProIdForRequest;
    document.getElementById('direct-request-title').value = '';
    document.getElementById('direct-request-desc').value = '';
    document.getElementById('direct-request-budget').value = '';
    document.getElementById('direct-request-feedback').innerHTML = '';
    
    var proProfileModal = document.getElementById('pro-profile-modal');
    var directRequestModal = document.getElementById('direct-request-modal');
    
    if (proProfileModal) {
        bootstrap.Modal.getOrCreateInstance(proProfileModal).hide();
    }
    if (directRequestModal) {
        bootstrap.Modal.getOrCreateInstance(directRequestModal).show();
    }
}

function sendDirectRequest() {
    var proId = document.getElementById('direct-request-pro-id').value;
    var title = document.getElementById('direct-request-title').value.trim();
    var description = document.getElementById('direct-request-desc').value.trim();
    var budget = document.getElementById('direct-request-budget').value;
    var feedback = document.getElementById('direct-request-feedback');
    var btn = document.getElementById('send-direct-request-btn');
    
    if (!title || !description) {
        feedback.innerHTML = '<div class="alert alert-danger mb-0">Please fill title and description</div>';
        return;
    }
    
    var token = localStorage.getItem('token');
    if (!token) {
        alert('Please login to send a request');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
    }

    fetch('/api/send-request/' + proId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
            title: title,
            description: description,
            budget: budget || null
        })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            feedback.innerHTML = '<div class="alert alert-success mb-0"><i class="fa-solid fa-check-circle me-1"></i> Request sent successfully!</div>';
            setTimeout(function() {
                bootstrap.Modal.getInstance(document.getElementById('direct-request-modal')).hide();
            }, 1500);
        } else {
            feedback.innerHTML = '<div class="alert alert-danger mb-0">' + (data.message || 'Failed to send request') + '</div>';
        }
    })
    .catch(function(err) {
        feedback.innerHTML = '<div class="alert alert-danger mb-0">Error: ' + (err.message || 'Failed to send request') + '</div>';
    })
    .finally(function() {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Request';
        }
    });
}

function showProProfile(proId) {
    currentProIdForRequest = proId;
    
    var modalBody = document.getElementById("pro-profile-modal-body");
    if (modalBody) {
        modalBody.innerHTML = '<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>';
    }
    
    var modalEl = document.getElementById("pro-profile-modal");
    if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // Show/hide hire button based on login and role
    var hireBtn = document.getElementById("hire-pro-btn");
    var token = localStorage.getItem("token");
    var role = localStorage.getItem("role");
    if (hireBtn) {
        if (token && role === "client") {
            hireBtn.classList.remove("d-none");
        } else {
            hireBtn.classList.add("d-none");
        }
    }
    
    var headers = {"Accept": "application/json"};
    if (token) headers["Authorization"] = "Bearer " + token;

    fetch("/api/professionals/" + proId, {method: "GET", headers: headers})
    .then(function(r) { 
        if (!r.ok) throw new Error("Request failed"); 
        return r.json(); 
    })
    .then(function(data) {
        var pro = data.professional || {};
        var name = pro.user ? pro.user.name : (pro.name || "N/A");
        var email = pro.user ? pro.user.email : "N/A";
        var photo = pro.profile_photo ? "/storage/" + pro.profile_photo : "/images/user1.jpg";
        var skill = pro.skill || "N/A";
        var location = pro.location || "N/A";
        var experience = pro.experience || "0";
        var rating = data.average_rating || 0;
        var bio = pro.bio || "No biography available.";
        var completedJobs = data.completed_jobs ? data.completed_jobs.length : 0;
        var reviews = data.reviews || [];
        var reportsCount = data.reports_count || 0;

        function generateStars(r) {
            var html = '';
            var fullStars = Math.floor(r);
            for (var i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    html += '<i class="fa-solid fa-star" style="color: #ffc107;"></i>';
                } else {
                    html += '<i class="fa-solid fa-star" style="color: #e4e5e9;"></i>';
                }
            }
            return html;
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            var date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
        }

        var reviewsHtml = '';
        if (reviews.length > 0) {
            reviews.forEach(function(review) {
                reviewsHtml += '<div class="border-bottom pb-3 mb-3">' +
                    '<div class="d-flex justify-content-between mb-2">' +
                    '<strong><i class="fa-solid fa-user me-1"></i>' + (review.reviewer_name || 'Anonymous') + '</strong>' +
                    '<span>' + generateStars(review.rating) + '</span>' +
                    '</div>' +
                    '<p class="mb-1 text-muted small">' + (review.comment || 'No comment') + '</p>' +
                    '<small class="text-muted">' + formatDate(review.created_at) + '</small>' +
                    '</div>';
            });
        } else {
            reviewsHtml = '<div class="alert alert-light border mb-0"><i class="fa-solid fa-star me-2"></i>No reviews yet</div>';
        }

        var reportsHtml = '';
        if (reportsCount > 0) {
            reportsHtml = '<div class="alert alert-danger py-2 mb-0"><i class="fa-solid fa-flag me-2"></i><strong>' + reportsCount + '</strong> report(s) filed against this professional</div>';
        } else {
            reportsHtml = '<div class="alert alert-success py-2 mb-0"><i class="fa-solid fa-check-circle me-2"></i>No reports filed</div>';
        }

        if (modalBody) {
            modalBody.innerHTML = `
                <div class="text-center mb-4">
                    <img src="${photo}" alt="${name}" class="rounded-circle object-fit-cover border border-3 border-primary" style="width: 120px; height: 120px;">
                    <h3 class="mt-3 mb-1 fw-bold">${name}</h3>
                    <div class="mb-2">${generateStars(rating)} <span class="text-muted">(${rating.toFixed(1)})</span></div>
                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-circle me-1"></i>Verified Professional</span>
                </div>
                
                ${reportsHtml}
                
                <div class="row g-3 mb-4 mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-info-circle me-2 text-primary"></i>Basic Info</h6>
                                <p class="mb-2"><i class="fa-solid fa-code me-2 text-secondary"></i><strong>Skill:</strong> ${skill}</p>
                                <p class="mb-2"><i class="fa-solid fa-location-dot me-2 text-secondary"></i><strong>Location:</strong> ${location}</p>
                                <p class="mb-2"><i class="fa-solid fa-briefcase me-2 text-secondary"></i><strong>Experience:</strong> ${experience} years</p>
                                <p class="mb-0"><i class="fa-solid fa-envelope me-2 text-secondary"></i><strong>Email:</strong> ${email}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-success"></i>Statistics</h6>
                                <div class="text-center">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <h2 class="fw-bold text-success mb-0">${completedJobs}</h2>
                                            <small class="text-muted">Completed Jobs</small>
                                        </div>
                                        <div class="col-6">
                                            <h2 class="fw-bold text-warning mb-0">${data.reviews_count || 0}</h2>
                                            <small class="text-muted">Reviews</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-user me-2 text-info"></i>About</h6>
                        <p class="mb-0" style="white-space: pre-wrap;">${bio}</p>
                    </div>
                </div>
                
                <div class="card border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-star me-2 text-warning"></i>Reviews (${reviews.length})</h6>
                        ${reviewsHtml}
                    </div>
                </div>
            `;
        }
    })
    .catch(function(err) {
        console.error(err);
        if (modalBody) {
            modalBody.innerHTML = '<div class="alert alert-danger mb-0">Failed to load profile. Please try again.</div>';
        }
    });
}

// Expose functions globally
window.showProProfile = showProProfile;
window.showDirectRequestModal = showDirectRequestModal;
window.sendDirectRequest = sendDirectRequest;
