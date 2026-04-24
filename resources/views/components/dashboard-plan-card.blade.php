@php
    $idPrefix = isset($idPrefix) && $idPrefix !== '' ? $idPrefix.'-' : '';
    $planTitle = $planTitle ?? 'Current Plan';
    $capacityLabel = $capacityLabel ?? 'Plan Capacity';
    $durationLabel = $durationLabel ?? 'Duration';
    $expiryLabel = $expiryLabel ?? 'Expires';
    $requestsLabel = $requestsLabel ?? 'Direct Requests';
    $gradientStart = $gradientStart ?? '#2d665b';
    $gradientEnd = $gradientEnd ?? '#1e4a42';
    $borderColor = $borderColor ?? '#28a745';
@endphp

<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, {{ $gradientStart }}, {{ $gradientEnd }}); border-top: 4px solid {{ $borderColor }} !important;">
    <div class="card-body py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-25 rounded p-2">
                    <i class="fa-solid fa-crown text-warning fs-4"></i>
                </div>
                <div>
                    <p class="text-white-50 small mb-0 text-uppercase">{{ $planTitle }}</p>
                    <h5 class="text-white mb-0 fw-bold" id="{{ $idPrefix }}current-plan-name">Loading...</h5>
                </div>
            </div>
            <div class="d-flex align-items-center gap-4">
                <div class="text-center" style="min-width: 110px;">
                    <p class="text-white-50 small mb-0 text-uppercase">{{ $capacityLabel }}</p>
                    <p class="text-white mb-0 fw-semibold" id="{{ $idPrefix }}current-plan-jobs">--</p>
                </div>
                <div class="text-center" style="min-width: 80px;">
                    <p class="text-white-50 small mb-0 text-uppercase">{{ $durationLabel }}</p>
                    <p class="text-white mb-0 fw-semibold" id="{{ $idPrefix }}current-plan-duration">--</p>
                </div>
                <div class="text-center" style="min-width: 100px;">
                    <p class="text-white-50 small mb-0 text-uppercase">{{ $expiryLabel }}</p>
                    <p class="text-white mb-0 fw-semibold" id="{{ $idPrefix }}current-plan-expiry">--</p>
                </div>
                <div class="text-center" style="min-width: 130px;">
                    <p class="text-white-50 small mb-0 text-uppercase">{{ $requestsLabel }}</p>
                    <p class="text-white mb-0 fw-semibold" id="{{ $idPrefix }}current-plan-requests">--</p>
                </div>
            </div>
        </div>
    </div>
</div>
