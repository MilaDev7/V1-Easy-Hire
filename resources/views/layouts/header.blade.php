 <!-- navbar -->


 <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
     <div class="container">

         <!-- Logo -->
         <a class="navbar-brand fw-bold text-success" href="/">
             EasyHire
         </a>

         <!-- Toggle (mobile) -->
         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
             <span class="navbar-toggler-icon"></span>
         </button>

         <!-- Links -->
         <div class="collapse navbar-collapse" id="nav">
             <ul class="navbar-nav mx-auto">
                 <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                 <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                 <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                 <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
             </ul>
             <!-- Auth Buttons -->
             <div id="guestSection" class="d-flex gap-2">
                 <a href="/register" class="btn btn-success">Register</a>
                 <a href="/login" class="btn btn-outline-success">Login</a>
             </div>

             <div id="userSection" class="d-flex ms-auto align-items-center gap-3">

                 <div class="d-flex align-items-center gap-2">
                     <img id="header-client-photo"
                         src="{{ asset('images/user1.jpg') }}"
                         alt="Client Profile"
                         class="rounded-circle border"
                         style="width: 36px; height: 36px; object-fit: cover;">

                     <span id="header-client-name" class="fw-semibold text-dark">Client</span>
                 </div>

                 <a id="header-account-link" href="/client/dashboard" class="btn btn-outline-success btn-sm">
                     Account
                 </a>
             </div>
         </div>

     </div>
 </nav>