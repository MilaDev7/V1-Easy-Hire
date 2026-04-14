@extends('layouts.app')
@section('content')
<div class="container py-5">
    <div class="card shadow border-0 p-4">
        <h3 class="fw-bold text-center mb-4">Complete Professional Profile</h3>
        <form id="setupForm">
            <div class="row">
                <div class="col-md-6">
                    <label>Profile Photo</label>
                    <input type="file" id="f_photo" class="form-control mb-3" accept="image/*" required>

                    <label>National ID Photo</label>
                    <input type="file" id="f_id" class="form-control mb-3" accept="image/*" required>

                    <!-- Replace the Skill Input with this Dropdown -->
                    <label class="fw-bold mb-1">Primary Skill</label>
                    <select id="f_skill" class="form-select mb-3" required>
                        <option value="">Select your profession...</option>
                        <optgroup label="Construction & Technical">
                            <option value="Electrician">Electrician</option>
                            <option value="Plumber">Plumber</option>
                            <option value="Carpenter">Carpenter</option>
                            <option value="Painter">Painter</option>
                            <option value="Welder">Welder</option>
                            <option value="Elevator Technician">Elevator Technician</option>
                            <option value="Auto Mechanic">Auto Mechanic</option>
                            <option value="Construction Worker">Construction Worker</option>
                        </optgroup>
                        <optgroup label="Home Services">
                            <option value="Cleaner">Cleaner / Housekeeping</option>
                            <option value="Gardener">Gardener</option>
                            <option value="Laundry">Laundry Specialist</option>
                            <option value="Chef">Private Chef / Cook</option>
                        </optgroup>
                        <optgroup label="Digital & Creative">
                            <option value="Graphic Designer">Graphic Designer</option>
                            <option value="Web Developer">Web Developer</option>
                            <option value="Digital Marketer">Digital Marketer</option>
                        </optgroup>
                        <optgroup label="Other Professionals">
                            <option value="Tutor">Private Tutor</option>
                            <option value="Driver">Driver</option>
                            <option value="Security">Security Guard</option>
                        </optgroup>
                    </select>
                    <label>Experience (Years)</label>
                    <input type="number" id="f_exp" class="form-control mb-3" required>

                    <label>Location (City)</label>
                    <select id="f_loc" name="location" class="form-select" required="">
                        <option value="">Select city</option>
                        <option value="Addis Ababa">Addis Ababa</option>
                        <option value="Adama">Adama</option>
                        <option value="Bahir Dar">Bahir Dar</option>
                        <option value="Hawassa">Hawassa</option>
                        <option value="Mekelle">Mekelle</option>
                        <option value="Dire Dawa">Dire Dawa</option>
                        <option value="Jimma">Jimma</option>
                        <option value="Dessie">Dessie</option>
                        <option value="Gondar">Gondar</option>
                        <option value="Bishoftu">Bishoftu</option>
                        <option value="Arba Minch">Arba Minch</option>
                        <option value="Harar">Harar</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Bio</label>
                    <textarea id="f_bio" class="form-control mb-3" rows="3" required></textarea>

                    <div class="row">
                        <div class="col-6"><label>Age</label><input type="number" id="f_age" class="form-control mb-3"></div>
                        <div class="col-6">
                            <label>Gender</label>
                            <select id="f_gender" class="form-select mb-3">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <label>CV (PDF Only)</label>
                    <input type="file" id="f_cv" class="form-control mb-3" accept=".pdf" required>

                    <label>Certificate (PDF/Image)</label>
                    <input type="file" id="f_cert" class="form-control mb-3">
                </div>
            </div>
            <button type="button" onclick="completePro()" class="btn btn-dark w-100 py-3 fw-bold">Finish & Submit for Review</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function completePro() {
        const fd = new FormData();

        // Validate required fields
        const skill = document.getElementById("f_skill").value;
        const exp = document.getElementById("f_exp").value;
        const bio = document.getElementById("f_bio").value;
        const location = document.getElementById("f_loc").value;
        const photo = document.getElementById("f_photo").files[0];
        const idCard = document.getElementById("f_id").files[0];
        const cv = document.getElementById("f_cv").files[0];

        if (!skill || !exp || !bio || !location || !photo || !idCard || !cv) {
            alert("Please fill all required fields and upload all required documents.");
            return;
        }

        if (bio.length < 20) {
            alert("Bio must be at least 20 characters.");
            return;
        }

        fd.append("skill", skill);
        fd.append("experience", exp);
        fd.append("bio", bio);
        fd.append("age", document.getElementById("f_age").value);
        fd.append("gender", document.getElementById("f_gender").value);
        fd.append("location", location);

        fd.append("profile_photo", photo);
        fd.append("id_card", idCard);
        fd.append("cv", cv);

        if (document.getElementById("f_cert").files[0]) {
            fd.append("certificate", document.getElementById("f_cert").files[0]);
        }

        fetch("/api/pro/complete-profile", {
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("token"),
                    "Accept": "application/json"
                },
                body: fd
            })
            .then(async res => {
                const data = await res.json();
                console.log("Response:", data);

                if (!res.ok) {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join("\n");
                        alert(errorMessages);
                    } else {
                        alert(data.message || "Something failed.");
                    }
                    return;
                }

                localStorage.setItem("approval_status", "pending");
                alert("Admin is under review.");
                window.location.href = "/pro/dashboard";
            })
            .catch(err => {
                console.error("ERROR:", err);
                alert("Server connection failed. Please try again.");
            });
    }
</script>
@endsection