<section
    class="client-subscription-section px-3 px-md-4 pb-4"
    style="margin-left: 280px;"
>
    <div
        class="rounded-4 p-3 p-md-4"
        style="background: linear-gradient(135deg, #1e4a42, #2d665b); box-shadow: 0 12px 28px rgba(13, 31, 27, 0.12);"
    >
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
            <div>
                <p class="mb-0 text-uppercase fw-semibold small" style="letter-spacing: 0.14em; color: rgba(220, 244, 230, 0.7);">
                    Subscription Plans
                </p>
                <h6 class="fw-bold mb-0 text-white">Upgrade Your Plan</h6>
            </div>
            <span
                class="px-3 py-1 rounded-pill small fw-semibold"
                style="background: rgba(255, 255, 255, 0.10); color: #e3f6eb; width: fit-content;"
            >
                Flexible hiring plans
            </span>
        </div>

        <div id="client-plans-container" class="row g-2">
            <div class="col-12 text-center text-white-50 py-5">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading plans...
            </div>
        </div>
    </div>
</section>

<script>
    async function loadClientPlans() {
        const container = document.getElementById('client-plans-container');
        if (!container) return;

        try {
            const response = await fetch('/api/plans');
            const plans = await response.json();
            
            if (!plans || plans.length === 0) {
                container.innerHTML = '<div class="col-12 text-center text-white-50 py-4">No plans available at the moment.</div>';
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
                const directRequests = plan.direct_requests_limit > 0 
                    ? `${plan.direct_requests_limit} Direct Requests`
                    : 'No Direct Requests';

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
                                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${directRequests}</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <p class="mb-0 small" style="color: rgba(255, 255, 255, 0.78);">${plan.duration_days || 0} Days</p>
                                    <button class="btn btn-light btn-sm rounded-pill fw-semibold px-3 py-1" onclick="window.buyPlan(${plan.id})">Buy Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            container.innerHTML = '<div class="col-12 text-center text-white-50 py-4">Failed to load plans. Please refresh.</div>';
        }
    }

    window.loadClientPlans = loadClientPlans;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.client-subscription-section')) {
            loadClientPlans();
        }
    });
</script>
