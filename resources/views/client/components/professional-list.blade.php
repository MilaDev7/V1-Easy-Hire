<section class="professional-list-section">
    <div class="card border-0 shadow-sm mb-4" style="border-top: 4px solid #6f42c1 !important;">
        <div class="card-body p-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 text-secondary">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>
                        <input
                            id="professional-skill"
                            class="form-control form-control-lg border-0 ps-1"
                            placeholder="Skill (e.g., Electrician)"
                        >
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 text-secondary">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <input
                            id="professional-location"
                            class="form-control form-control-lg border-0 ps-1"
                            placeholder="Location (e.g., Addis)"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <button
                        type="button"
                        id="professional-search-button"
                        class="btn btn-success btn-sm w-100 rounded-pill fw-bold h-100 d-flex align-items-center justify-content-center gap-2"
                        onclick="searchProfessionals()"
                    >
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="professionals-results">
        <div class="text-muted">Loading professionals...</div>
    </div>
</section>

<script>
    function searchProfessionals() {
        var skill = document.getElementById('professional-skill').value;
        var location = document.getElementById('professional-location').value;
        
        var params = new URLSearchParams({
            skill: skill,
            location: location
        });

        var token = localStorage.getItem('token');
        var headers = {
            'Accept': 'application/json'
        };
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }

        fetch('/api/professionals?' + params.toString(), {
            method: 'GET',
            headers: headers
        })
        .then(function(response) {
            if (!response.ok) throw new Error("Request failed");
            return response.json();
        })
        .then(function(payload) {
            var professionals = payload.data || [];
            renderProfessionalsResults(professionals);
        })
        .catch(function() {
            var results = document.getElementById("professionals-results");
            if (results) {
                results.innerHTML = '<div class="alert alert-danger">Unable to load professionals.</div>';
            }
        });
    }

    function renderProfessionalsResults(professionals) {
        var results = document.getElementById("professionals-results");
        if (!results) return;

        if (!professionals.length) {
            results.innerHTML = '<div class="alert alert-light border mb-0">No professionals found.</div>';
            return;
        }

        var cards = professionals.map(function(pro) {
            var name = pro.user ? pro.user.name : (pro.name || 'N/A');
            var photo = pro.profile_photo ? '/storage/' + pro.profile_photo : '/images/user1.jpg';
            var skill = pro.skill || 'N/A';
            var location = pro.location || 'N/A';
            var rating = pro.average_rating || 0;

            return '<div class="col-md-6 col-xl-4">' +
                '<div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">' +
                '<div class="card-body p-4">' +
                '<div class="d-flex align-items-center mb-3">' +
                '<img src="' + photo + '" alt="' + name + '" class="rounded-circle object-fit-cover me-3 border" style="width: 72px; height: 72px;">' +
                '<div><h3 class="h6 mb-1 fw-bold">' + name + '</h3><div class="text-warning small">★ ' + rating.toFixed(1) + '</div></div>' +
                '</div>' +
                '<p class="mb-2"><span class="fw-semibold">Skills:</span> ' + skill + '</p>' +
                '<p class="mb-2"><span class="fw-semibold">Location:</span> ' + location + '</p>' +
                '</div></div></div>';
        }).join("");

        results.innerHTML = '<div class="row g-4">' + cards + '</div>';
    }
</script>
