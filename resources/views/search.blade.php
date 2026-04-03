@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4 fw-bold">Search Results</h3>

        <!-- Loading Spinner (Hidden by default) -->
        <div id="loading" class="text-center my-5">
            <div class="spinner-border text-success" role="status"></div>
            <p>Finding experts...</p>
        </div>

        <!-- RESULTS GRID -->
        <div id="results" class="row g-4"></div>

        <!-- EMPTY MESSAGE -->
        <div id="noResults" class="text-center text-muted mt-5" style="display:none;">
            <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
            <h5>No professionals found matching your search</h5>
            <p>Try searching for a different city or service.</p>
            <a href="/" class="btn btn-outline-dark mt-3">Back to Home</a>
        </div>
        </div>

        <!-- Modal for Unauthenticated Users -->
        <div class="modal fade" id="authModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4">
                    <div class="text-center">
                        <h5 class="fw-bold">Want to contact this pro?</h5>
                        <p class="text-muted">You need to register or login first.</p>
                        <div class="d-grid gap-2">
                            <a href="/login" class="btn btn-success">Login Now</a>
                            <a href="/register" class="btn btn-outline-dark">Create Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
    
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);

            // Rename to avoid reserved global 'location'
            const targetLocation = params.get("location") || "";
            const targetService = params.get("service") || "";

            const resultsDiv = document.getElementById("results");
            const noResults = document.getElementById("noResults");
            const loading = document.getElementById("loading");

            console.log("Searching for:", targetLocation, targetService);

            // FETCH DATA
            fetch(`/api/professionals?location=${targetLocation}&skill=${targetService}`)
                .then(res => res.json())
                .then(data => {
                    // 1. Hide the spinner
                    if (loading) loading.style.display = "none";

                    console.log("API DATA:", data);

                    // 2. Handle the "data" wrapper (Laravel creates this)
                    const professionals = data.data ? data.data : data;

                    // 3. Check if we actually got results
                    if (!professionals || professionals.length === 0) {
                        if (noResults) noResults.style.display = "block";
                        return;
                    }

                    // 4. Build the HTML cards
                    let html = "";
                    professionals.forEach(pro => {
                        html += `
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3 p-3 text-center">
                        <img src="/images/default-user.jpg" class="rounded-circle mx-auto mb-3" style="width:80px; height:80px; object-fit:cover;">
                        <h5 class="fw-bold mb-1">${pro.user ? pro.user.name : 'Verified Pro'}</h5>
                        <p class="text-muted small mb-2">${pro.skill ?? "Professional"}</p>
                        <div class="text-warning mb-3">
                             <i class="fas fa-star text-warning"></i> 4.5
                        </div>
                       <button class="btn btn-outline-success w-100" onclick="handleProClick(${pro.id})">
    View Profile
</button>
                    </div>
                </div>`;
                    });

                    // 5. Inject into the DOM
                    if (resultsDiv) resultsDiv.innerHTML = html;

                }) // This closes the second .then
                .catch(err => {
                    if (loading) loading.style.display = "none";
                    console.error("CRITICAL ERROR:", err);
                }); // This closes the fetch/catch
        }); // This closes the DOMContentLoaded listener

        function handleProClick() {
            const modalElement = document.getElementById('authModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }
    </script>
@endsection