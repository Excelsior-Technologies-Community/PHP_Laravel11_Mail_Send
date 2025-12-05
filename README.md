Laravel 11 Mail Send Project Tutorial

By: Manasi Patel / Laravel11-Mail
Date: 2025
Laravel Version: 11

In this tutorial, we will build a Mail Send Project using Laravel 11. Users will be able to send emails via a form, view logs of sent emails, and manage them. This tutorial is beginner-friendly, step-by-step, and fully commented.

We will cover:

Installing Laravel 11 and setting up the environment

Configuring the database

Creating a migration for mails table

Building Eloquent models

Creating a resource controller for mail operations

Defining routes

Designing the frontend with Blade templates and Bootstrap 5

Sending emails using Laravel's Mailables

Viewing mail logs in a table


Step 1: Install Laravel 11

Install a fresh Laravel 11 project:

composer create-project laravel/laravel laravel11-mail "^11.0"

Navigate to your project folder:

cd MailSendApp

Your Laravel 11 project is ready.


Step 2: Configure Database

Open .env and update database credentials:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel11-mail
DB_USERNAME=root
DB_PASSWORD=


Create a database named mail_app in phpMyAdmin or MySQL CLI:

CREATE DATABASE mail_app;


Step 3: Create Migration for mails Table

Create migration:

php artisan make:migration create_mail_logs_table --create=mails

Open the generated migration in database/migrations/xxxx_xx_xx_create_mails_table.php and add:

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create 'mail_logs' table
        Schema::create('mail_logs', function (Blueprint $table) {

            $table->id(); 
            // Primary key 'id', auto-increment

            $table->string('email'); 
            // Recipient email address

            $table->string('subject'); 
            // Email subject

            $table->longText('message'); 
            // Full email message content

            // Extra tracking fields
            $table->unsignedBigInteger('created_by')->nullable(); 
            // ID of user who created the mail log, nullable

            $table->unsignedBigInteger('updated_by')->nullable(); 
            // ID of user who last updated the mail log, nullable

            $table->softDeletes(); 
            // Adds 'deleted_at' column for soft deletes

            $table->tinyInteger('status')->default(1); 
            // Status: 1=Active, 0=Inactive, default is Active

            $table->timestamps(); 
            // Adds 'created_at' and 'updated_at' columns automatically
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop 'mail_logs' table if it exists
        Schema::dropIfExists('mail_logs');
    }
};


Run migration:

php artisan migrate


Step 4: Configure Mail Settings

Open .env and configure your SMTP or Mailtrap (for testing):

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="from@example.com"
MAIL_FROM_NAME="Laravel11-Mail"


Step 5: Create Model & Controller

Create a MailLog model and resource controller:

php artisan make:model MailLog -m
php artisan make:controller MailController --resource --model=MailLog


MailLog Model

app/Models/MailLog.php:

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes; 
    // Enables soft delete functionality (adds deleted_at column support)

    /**
     * Mass assignable fields
     * These fields can be filled using create() or update() methods
     */
    protected $fillable = [
        'email',        // Recipient email address
        'subject',      // Email subject
        'message',      // Email message content
        'created_by',   // User ID who created the mail log
        'updated_by',   // User ID who last updated the mail log
        'status'        // Status of the mail log: 1=Active, 0=Inactive
    ];
}



MailController

app/Http/Controllers/MailController.php:

<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;          // Mailable class for sending emails
use App\Models\MailLog;         // Mail log model
use Illuminate\Http\Request;    // HTTP request
use Illuminate\Support\Facades\Mail; // Facade for sending emails

class MailController extends Controller
{
    /**
     * Show the mail form
     */
    public function index()
    {
        return view('mail.form'); // Display the email form
    }

    /**
     * Show the create form (same as index)
     */
    public function create()
    {
        return view('mail.form'); // your form.blade.php
    }

    /**
     * Send email and save log
     */
    public function send(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email'   => 'required|email',  // Must be a valid email
            'subject' => 'required',        // Subject required
            'message' => 'required',        // Message required
        ]);

        // Save the mail log to database
        $log = MailLog::create([
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'created_by' => 1,   // Dummy user ID, replace with Auth::id() later
            'status'     => 1,   // 1 = Active
        ]);

        // Prepare mail details for Mailable
        $details = [
            'title' => $request->subject,
            'body'  => $request->message,
        ];

        // Send the email using TestMail Mailable
        Mail::to($request->email)->send(new TestMail($details));

        // Redirect to success page
        return view('mail.success');
    }

    /**
     * List all active mails with pagination
     */
    public function list()
    {
        $mails = MailLog::where('status', 1) // Only active mails
                        ->orderBy('id', 'ASC') 
                        ->paginate(10); // Paginate 10 per page

        return view('mail.index', compact('mails'));
    }

    /**
     * View a single mail
     */
    public function view($id)
    {
        $mail = MailLog::findOrFail($id); // Find by ID or fail
        return view('mail.view', compact('mail'));
    }

    /**
     * Soft delete a mail
     */
    public function delete($id)
    {
        $mail = MailLog::findOrFail($id); // Fetch mail
        $mail->delete(); // Soft delete (sets deleted_at)
        return redirect()->back()->with('success', 'Mail deleted successfully!');
    }

    /**
     * Restore a soft deleted mail
     */
    public function restore($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id); // Include trashed
        $mail->restore(); // Restore deleted mail
        return redirect()->back()->with('success', 'Email restored successfully!');
    }

    /**
     * Permanently delete a mail
     */
    public function forceDelete($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id); // Include trashed
        $mail->forceDelete(); // Permanently remove from DB
        return redirect()->back()->with('success', 'Email permanently deleted!');
    }

    /**
     * Change mail status (Active/Inactive)
     */
    public function changeStatus($id)
    {
        $mail = MailLog::findOrFail($id); // Fetch mail

        // Toggle status
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();

        return redirect()->back()->with('success', 'Status updated!');
    }
}



Step 6: Add Routes

routes/web.php:

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

// Default homepage
Route::get('/', function () {
    return view('welcome');
});

// ================================
// MAIL MODULE ROUTES
// ================================

// Show email form to send mail
Route::get('/email', [MailController::class, 'index']);      

// Process sending of email
Route::post('/send-email', [MailController::class, 'send']); 

// ================================
// ADMIN PANEL ROUTES FOR MAIL LOGS
// ================================

// List all mails (paginated)
Route::get('/mail', [MailController::class, 'list']);                

// View details of a single mail by ID
Route::get('/mail/view/{id}', [MailController::class, 'view']);      

// Soft delete a mail (moves to trashed)
Route::get('/mail/delete/{id}', [MailController::class, 'delete']);  

// Restore a soft-deleted mail
Route::get('/mail/restore/{id}', [MailController::class, 'restore']); 

// Permanently delete a mail from database
Route::get('/mail/force-delete/{id}', [MailController::class, 'forceDelete']); 

// Toggle mail status between active (1) and inactive (0)
Route::get('/mail/status/{id}', [MailController::class, 'changeStatus']); 


Step 7: Create Blade Views

Create a folder resources/views/layouts/ and create these files and write code:

resources/views/layouts/app.blade.php

<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title>
    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .navbar {
            /* Navbar styling */
            margin-bottom: 20px;
            text-align: center;
        }
        .card {
            /* Rounded corners for cards */
            border-radius: 15px;
        }
        body {
            /* Page background color */
            background: #f5f6fa;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark w-100 shadow-sm">
    <div class="container-fluid d-flex justify-content-center">
        <span class="navbar-brand mb-0 h1 text-center" style="font-size: 22px;">
            Mail Sender
        </span>
    </div>
</nav>

<!-- Main container for child views -->
<div class="container">
    @yield('content') <!-- Child views will be injected here -->
</div>

</body>
</html>



Create a folder resources/views/emails/ and create these files and write code:

resources/views/emails/test.blade.php

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $details['title'] }}</title>
    <style>
        /* Reset some styles for email clients */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        
        /* Main email container */
        .email-container {
            max-width: 600px;
            margin: 40px auto; /* Center the email */
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        /* Header section */
        .email-header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        /* Body section */
        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }

        /* Footer section */
        .email-footer {
            background-color: #f1f1f1;
            color: #555555;
            text-align: center;
            padding: 15px;
            font-size: 12px;
        }

        /* Button styling (optional) */
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        /* Responsive for small screens */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f4f4f4">
        <tr>
            <td>
                <table class="email-container" cellpadding="0" cellspacing="0">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <h1>{{ $details['title'] }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="email-body">
                            <p>{{ $details['body'] }}</p>
                            <!-- Example button (optional) -->
                            {{-- <a href="#" class="btn">View Details</a> --}}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            &copy; {{ date('Y') }} Your Company. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>



Create a folder resources/views/mails/ and create these files and write code:

resources/views/mails/index.blade.php

@extends('layouts.app')

@section('content')
<div class="card shadow-lg">
    <!-- Card Header: Title + Send Email Button -->
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Mail Logs</h4>
        <!-- Button to open Send Email Form -->
        <a href="{{ url('/email') }}" class="btn btn-success btn-sm mb-3">
            Send Email
        </a>
    </div>

    <!-- Card Body -->
    <div class="card-body">

        <!-- Display Success Message -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Table: List of Mail Logs -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            @forelse($mails as $mail)
                <tr>
                    <td>{{ $mail->id }}</td>
                    <td>{{ $mail->email }}</td>
                    <td>{{ $mail->subject }}</td>
                    <td>
                        <!-- Status Badge -->
                        @if($mail->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $mail->created_at->format('d-m-Y') }}</td>
                    <td>
                        <!-- View Mail Details -->
                        <a href="{{ url('/mail/view/'.$mail->id) }}" class="btn btn-sm btn-primary">View</a>
                        <!-- Soft Delete Mail -->
                        <a href="{{ url('/mail/delete/'.$mail->id) }}" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this mail?');">
                            Delete
                        </a>
                    </td>
                </tr>
            @empty
                <!-- Show if no mails -->
                <tr>
                    <td colspan="6" class="text-center">No Email Logs Found</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $mails->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Optional: Modal for Sending Email (if needed in future) -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Form to send email -->
      <form action="{{ url('/mail/send') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="sendEmailModalLabel">Send Email</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <!-- Recipient Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Recipient Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <!-- Subject -->
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <!-- Message -->
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <!-- Modal Buttons -->
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Send Email</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


resources/views/mails/form.blade.php

@extends('layouts.app')
@section('content')
<br>
<div class="row justify-content-center">
    <div class="col-md-6">

        <!-- Card Container for Form -->
        <div class="card shadow-lg">

            <!-- Card Header -->
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Send Email</h4>
            </div>

            <!-- Card Body: Form Starts -->
            <div class="card-body">
                <!-- Form POSTs to send email route -->
                <form action="/send-email" method="POST">
                    @csrf <!-- CSRF token for security -->

                    <!-- Recipient Email Input -->
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <!-- Email Subject Input -->
                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <!-- Email Message Input -->
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success w-100">
                        Send Email
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

resources/views/mails/view.blade.php

@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            <!-- Card Container for Email Details -->
            <div class="card border-0 shadow-lg rounded-4">

                <!-- Card Header with Gradient Background -->
                <div class="card-header text-white text-center py-4 rounded-top-4" 
                     style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
                    <h3 class="mb-0">
                        <i class="bi bi-envelope-fill me-2"></i>Email Details
                    </h3>
                </div>

                <!-- Card Body -->
                <div class="card-body p-4">

                    <!-- Recipient Email Section -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Recipient Email</h6>
                        <div class="p-3 border rounded bg-light text-break">
                            {{ $mail->email }}
                        </div>
                    </div>

                    <!-- Email Subject Section -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Subject</h6>
                        <div class="p-3 border rounded bg-light text-break">
                            {{ $mail->subject }}
                        </div>
                    </div>

                    <!-- Email Message Section -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Message</h6>
                        <!-- Preserve line breaks using pre-wrap -->
                        <div class="p-3 border rounded bg-light" style="white-space: pre-wrap;">
                            {{ $mail->message }}
                        </div>
                    </div>

                    <!-- Status & Created At Info Boxes -->
                    <div class="row mb-4 g-3 text-center">

                        <!-- Status Box -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded shadow-sm bg-white">
                                <h6 class="text-muted fw-bold mb-2">
                                    <i class="bi bi-toggle-on me-2"></i>Status
                                </h6>
                                <!-- Show Active/Inactive badge -->
                                @if($mail->status == 1)
                                    <span class="badge bg-success px-3 py-2 fs-6">Active</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2 fs-6">Inactive</span>
                                @endif

                                <!-- Show Deleted badge if soft deleted -->
                                @if($mail->deleted_at)
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6 ms-1">Deleted</span>
                                @endif
                            </div>
                        </div>

                        <!-- Created At Box -->
                        <div class="col-md-6">
                            <div class="p-3 border rounded shadow-sm bg-white">
                                <h6 class="text-muted fw-bold mb-2">
                                    <i class="bi bi-calendar-check me-2"></i>Created At
                                </h6>
                                <div>{{ $mail->created_at->format('d-m-Y H:i A') }}</div>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <!-- Back to Mail List -->
                        <a href="{{ url('/mail') }}" class="btn btn-secondary btn-lg shadow-sm">Back</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons for icons like envelope and calendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection


resources/views/mails/success.blade.php

@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">

        <!-- Success Card -->
        <div class="card shadow-lg">

            <!-- Card Header -->
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Success!</h4>
            </div>

            <!-- Card Body -->
            <div class="card-body text-center">
                <!-- Success Icon and Message -->
                <h3 class="text-success">✔ Email Sent Successfully</h3>
                <p>Your message was delivered to the recipient.</p>

                <!-- Action Buttons -->
                <a href="/email" class="btn btn-primary mt-3">
                    Send Another Email
                </a>
                <a href="/mail" class="btn btn-primary mt-3">
                    List Emails
                </a>
            </div>
        </div>

    </div>
</div>

@endsection


Create a folder app/Mail/ and create these files:


use this:

php artisan make:mail TestMail



app/Mail/TestMail.php

<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    // Public property to store mail details (title and body)
    public $details;

    /**
     * Constructor to initialize the Mailable with details
     *
     * @param array $details - Should contain 'title' and 'body' keys
     */
    public function __construct($details)
    {
        $this->details = $details; // Store the details to be accessible in the email view
    }

    /**
     * Build the email message
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->details['title']) // Set the email subject
                    ->view('emails.test');           // Load the Blade view for email content
    }
}


Step 8: Run the Application

php artisan serve


Open browser:

 http://localhost:8000/mails




Now you can:

Send emails via a form

View email logs

Search by recipient or subject

Paginate logs

Soft delete logs

✅ Congratulations! You now have a fully functional Laravel 11 Mail Send Project.





