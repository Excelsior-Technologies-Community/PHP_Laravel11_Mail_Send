@extends('layouts.app') <!-- Extends the main layout -->

@section('content')
<br> <!-- Adds spacing at the top -->

<div class="row justify-content-center"> <!-- Center the form horizontally -->
    <div class="col-md-6"> <!-- Form takes 6 columns in medium devices -->

        <div class="card shadow-lg"> <!-- Card container with shadow -->
            <div class="card-header bg-primary text-white"> <!-- Card header with blue background and white text -->
                <h4 class="mb-0">Send Email</h4> <!-- Card title -->
            </div>

            <div class="card-body">
                <!-- Email form -->
                <form action="/send-email" method="POST">
                    @csrf <!-- CSRF token for security -->

                    <!-- Email input -->
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required> <!-- Required email input -->
                    </div>

                    <!-- Subject input -->
                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required> <!-- Required subject input -->
                    </div>

                    <!-- Message textarea -->
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea> <!-- Required message input -->
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="btn btn-success w-100">Send Email</button> <!-- Full-width submit button -->
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
