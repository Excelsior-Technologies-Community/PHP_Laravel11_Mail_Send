@extends('layouts.app') <!-- Extends the main layout -->

@section('content')

<div class="row justify-content-center"> <!-- Center the card horizontally -->
    <div class="col-md-6"> <!-- Card takes 6 columns on medium devices -->

        <div class="card shadow-lg"> <!-- Card with shadow for emphasis -->
            <div class="card-header bg-success text-white"> <!-- Header with green background and white text -->
                <h4 class="mb-0">Success!</h4> <!-- Card header title -->
            </div>

            <div class="card-body text-center"> <!-- Center-align text inside card body -->
                <h3 class="text-success">✔ Email Sent Successfully</h3> <!-- Success message with green text and check mark -->
                <p>Your message was delivered to the recipient.</p> <!-- Additional info -->

                <!-- Action buttons -->
                <a href="/email" class="btn btn-primary mt-3">Send Another Email</a> <!-- Navigate to send new email -->
                <a href="/mail" class="btn btn-primary mt-3">List Emails</a> <!-- Navigate to email logs -->
            </div>
        </div>

    </div>
</div>

@endsection
