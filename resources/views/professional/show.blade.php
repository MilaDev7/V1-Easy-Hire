@extends('layouts.app')

@section('content')
<div class="container py-5">
    @include('professional.partials.profile-content')

    <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center">
        <a href="/" class="btn btn-outline-primary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Home
        </a>
        <a href="/search" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Back to Search
        </a>
    </div>
</div>

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
                    <input type="text" class="form-control" id="direct-request-title">
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
    (function () {
        const professionalId = {{ (int) $professional->id }};
        const professionalName = @json($professional->user->name ?? 'Professional');
        const role = localStorage.getItem("role");

        const clientActions = document.getElementById("profile-actions-client");
        const guestActions = document.getElementById("profile-actions-guest");
        const adminActions = document.getElementById("profile-actions-admin");
        const hireButton = document.getElementById("action-hire-btn");
        const reviewButton = document.getElementById("action-review-btn");
        const reportButton = document.getElementById("action-report-btn");
        const reviewsSection = document.getElementById("profile-reviews-section");
        const reportsSection = document.getElementById("profile-reports-section");
        const showMoreReviewsButton = document.getElementById("profile-show-more-reviews-btn");

        if (role === "client") {
            if (clientActions) clientActions.classList.remove("d-none");
            if (typeof window.currentProIdForRequest !== "undefined") {
                window.currentProIdForRequest = professionalId;
            } else {
                window.currentProIdForRequest = professionalId;
            }
        } else if (role === "admin") {
            if (adminActions) adminActions.classList.remove("d-none");
        } else {
            if (guestActions) guestActions.classList.remove("d-none");
        }

        if (hireButton) {
            hireButton.addEventListener("click", function () {
                window.currentProIdForRequest = professionalId;
                if (typeof window.showDirectRequestModal === "function") {
                    window.showDirectRequestModal();
                }
            });
        }

        if (reviewButton) {
            reviewButton.addEventListener("click", function () {
                if (reviewsSection) {
                    reviewsSection.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        }

        if (reportButton) {
            reportButton.addEventListener("click", function () {
                if (reportsSection) {
                    reportsSection.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        }

        if (showMoreReviewsButton) {
            showMoreReviewsButton.addEventListener("click", function () {
                document.querySelectorAll(".profile-review-extra").forEach(function (item) {
                    item.classList.remove("d-none");
                });
                showMoreReviewsButton.classList.add("d-none");
            });
        }
    })();
</script>
@endsection
