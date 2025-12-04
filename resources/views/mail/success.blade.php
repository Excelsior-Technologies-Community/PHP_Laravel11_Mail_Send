@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Success!</h4>
            </div>

            <div class="card-body text-center">
                <h3 class="text-success">✔ Email Sent Successfully</h3>
                <p>Your message was delivered to the recipient.</p>

                <a href="/email" class="btn btn-primary mt-3">Send Another Email</a>
                <a href="/mail" class="btn btn-primary mt-3">List Emails</a>
            </div>
        </div>

    </div>
</div>

@endsection
