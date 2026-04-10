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
    </div>
@endsection
    
@section('scripts')
    <script>
        function handleProClick(professionalId) {
            window.location.href = `/professional/${professionalId}`;
        }
    </script>
@endsection
