@extends('layouts.app')

@section('content')

<div class="container py-5">

    <h3 class="mb-4">Search Results</h3>

    <div id="results" class="row g-4"></div>

</div>

<script>
const params = new URLSearchParams(window.location.search);
const location = params.get("location");
const service = params.get("service");

fetch(`http://127.0.0.1:8000/api/professionals?location=${location}&skill=${service}`)
.then(res => res.json())
.then(data => {
    let html = "";

    data.forEach(pro => {
        html += `
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">

                    <img src="/images/default-user.jpg" class="rounded mb-2">

                    <h5>${pro.name}</h5>

                    <p class="text-warning">★★★★★</p>

                    <p>${pro.skill}</p>

                    <button class="btn btn-outline-success"
                        onclick="requireAuth(${pro.id})">
                        View Profile
                    </button>

                </div>
            </div>
        `;
    });

    document.getElementById("results").innerHTML = html;
});

function requireAuth(proId) {
    const token = localStorage.getItem("token");

    if (!token) {
        // save where user wanted to go
        localStorage.setItem("redirect_after_login", `/professional/${proId}`);

        showAuthModal();
    } else {
        window.location.href = `/professional/${proId}`;
    }
}
</script>

@endsection