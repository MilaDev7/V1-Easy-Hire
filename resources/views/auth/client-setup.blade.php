@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card p-5 shadow-sm border-0 d-inline-block">
        <h2 class="fw-bold">Welcome, Client!</h2>
        <p class="text-muted mb-4">Add a profile photo to build trust with professionals.</p>
        
        <div class="mb-3">
            <input type="file" id="clientPhoto" class="form-control" accept="image/*">
        </div>

        <button onclick="uploadClient()" class="btn btn-primary px-5">Finish & Start Hiring</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
function uploadClient() {
    const file = document.getElementById("clientPhoto").files[0];
    if(!file) { window.location.href = "/client/dashboard"; return; }

    const fd = new FormData();
    fd.append("profile_photo", file);

    fetch("/api/client/update-photo", {
        method: "POST",
        headers: { "Authorization": "Bearer " + localStorage.getItem("token"), "Accept": "application/json" },
        body: fd
    })
    .then(res => res.json())
    .then(() => {
        alert("Profile Ready!");
        window.location.href = "/client/dashboard";
    });
}
</script>
@endsection