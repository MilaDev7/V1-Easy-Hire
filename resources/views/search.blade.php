@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4 fw-bold">Search Results</h3>

        @php
            $results = $professionals ?? collect();
        @endphp

        @if($results->isEmpty())
            <div id="noResults" class="text-center text-muted mt-5">
                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                <h5>No professionals found matching your search</h5>
                <p>Try searching for a different city or service.</p>
                <a href="/" class="btn btn-outline-dark mt-3">Back to Home</a>
            </div>
        @else
            <div id="results" class="row g-4">
                @foreach($results as $professional)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img
                                    src="{{ $professional->profile_photo ? asset('storage/' . $professional->profile_photo) : asset('images/user1.jpg') }}"
                                    class="rounded-circle border"
                                    alt="{{ $professional->user->name ?? 'Verified Pro' }}"
                                    style="width:80px; height:80px; object-fit:cover;"
                                >
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $professional->user->name ?? 'Verified Pro' }}</h5>
                                    <p class="text-muted small mb-1">{{ $professional->skill ?: 'Professional' }}</p>
                                    <p class="text-muted small mb-0">
                                        <i class="fa-solid fa-location-dot me-1"></i>{{ $professional->location ?: 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <button class="btn btn-outline-success w-100" onclick="handleProClick({{ $professional->id }})">
                                View Profile
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Modal for Unauthenticated Users -->
        <div class="modal fade" id="authModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4">
                    <div class="text-center">
                        <h5 class="fw-bold">Want to contact this pro?</h5>
                        <p class="text-muted">You need to register or login first.</p>
                        <div class="d-grid gap-2">
                            <a id="loginWithRedirect" href="/login" class="btn btn-success">Login Now</a>
                            <a href="/register" class="btn btn-outline-dark">Create Account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Professional Profile Modal -->
    <div class="modal fade" id="pro-profile-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-tie me-2"></i>Professional Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="pro-profile-modal-body">
                    <div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success d-none" id="hire-pro-btn" onclick="showDirectRequestModal()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Hire / Send Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Request Modal -->
    <div class="modal fade" id="direct-request-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-paper-plane me-2"></i>Send Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="direct-request-pro-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" class="form-control" id="direct-request-title" placeholder="e.g., Need web developer">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="direct-request-desc" rows="4" placeholder="Describe your project..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Budget (Optional)</label>
                        <input type="number" class="form-control" id="direct-request-budget" placeholder="e.g., 500">
                    </div>
                    <div id="direct-request-feedback"></div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="send-direct-request-btn" onclick="sendDirectRequest()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Request
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
    
@section('scripts')
    <script>
        function handleProClick(professionalId) {
            const token = localStorage.getItem("token");
            
            if (token) {
                showProProfile(professionalId);
                return;
            }

            localStorage.setItem('pending_pro_id', professionalId);
            
            const modalElement = document.getElementById('authModal');
            const loginLink = document.getElementById('loginWithRedirect');
            
            loginLink.href = '/login?redirect=' + encodeURIComponent(window.location.href);
            
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const proId = localStorage.getItem('pending_pro_id');
            if (proId) {
                localStorage.removeItem('pending_pro_id');
                showProProfile(proId);
            }
        });
    </script>
@endsection
