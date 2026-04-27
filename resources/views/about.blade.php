@extends('layouts.app')

@section('content')
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <h1 class="fw-bold mb-2">About EasyHire</h1>
        <p class="text-muted mb-0">A local hiring platform that connects clients with verified professionals quickly and safely.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">What It Does</h2>
                        <p class="mb-0 text-muted">
                            EasyHire helps people find trusted local professionals for real jobs. Clients can post jobs, review applicants, and hire with confidence.
                            Professionals can discover opportunities, apply quickly, and build reputation through completed contracts and reviews.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">For Clients</h2>
                        <ul class="text-muted mb-0">
                            <li>Post jobs with skill, location, and budget</li>
                            <li>Receive applications from relevant professionals</li>
                            <li>Send direct requests and manage contracts</li>
                            <li>Rate and review completed work</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">For Professionals</h2>
                        <ul class="text-muted mb-0">
                            <li>Browse open jobs and apply based on skills</li>
                            <li>Track contracts, requests, and applications</li>
                            <li>Build profile visibility and ratings</li>
                            <li>Purchase apply plans as needed</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">How It Works</h2>
                        <ol class="text-muted mb-0">
                            <li>Client posts a job or sends a direct request</li>
                            <li>Professional applies or accepts request</li>
                            <li>Contract starts and work is completed</li>
                            <li>Client confirms completion and leaves review</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
