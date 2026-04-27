@extends('layouts.app')

@section('content')
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <h1 class="fw-bold mb-2">Contact Us</h1>
        <p class="text-muted mb-0">Have a question or need support? Send us a message.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.submit') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="contact-name" class="form-label fw-semibold">Name</label>
                                <input type="text" class="form-control" id="contact-name" name="name" value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact-email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" id="contact-email" name="email" value="{{ old('email') }}" required>
                            </div>

                            <div class="mb-4">
                                <label for="contact-message" class="form-label fw-semibold">Message</label>
                                <textarea class="form-control" id="contact-message" name="message" rows="6" required>{{ old('message') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa-solid fa-paper-plane me-1"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
