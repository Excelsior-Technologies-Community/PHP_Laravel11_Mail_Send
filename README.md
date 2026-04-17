# Laravel 11 Mail Send Project

A beginner-friendly Laravel 11 project that allows users to send emails via a form, log sent emails, and manage them. It includes Laravel Mailables, soft deletes, status management, and a clean Bootstrap 5 frontend.

---

##  Features
- Send emails using a form
- Save email logs in the database
- View all email logs with pagination
- Soft delete, restore, and permanently delete email logs
- Toggle email log status (Active/Inactive)
- View details of a single email
- Responsive UI with Bootstrap 5
- Beginner-friendly, fully commented code

---

##  Requirements
- PHP 8.1+
- Laravel 11
- MySQL
- Composer
- SMTP account (Gmail SMTP with App Password)


---

##  Installation Steps

### Step 1: Install Laravel 11
```
composer create-project laravel/laravel laravel11-mail "^11.0"
cd laravel11-mail
```
Step 2: Configure Database
Edit .env:
```
env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mail_app
DB_USERNAME=root
DB_PASSWORD=
Create the database:

sql
```
CREATE DATABASE mail_app;



Step 3: Create Migration for Mail Logs
```
php artisan make:migration create_mail_logs_table --create=mail_logs

```

Migration file (database/migrations/..._create_mail_logs_table.php):

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('subject');
            $table->longText('message');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
```
Run migration:

```

php artisan migrate
```
Step 4: Configure Mail Settings


### Step 4.1: Login to Gmail Account

1. Open :https://mail.google.com

2. Login with your Gmail account

Example:

```
example@gmail.com

```

### Step 4.2: Enable 2-Step Verification (Mandatory)

1. Open https://myaccount.google.com/security

2. Under “Signing in to Google”

3. Enable 2-Step Verification

4. Verify using OTP on mobile

 After enabling this, App Password option will appear

<img width="1793" height="910" alt="Screenshot 2026-01-29 162503" src="https://github.com/user-attachments/assets/65b82e57-8314-4f2c-9678-d35184945fef" />


### Step 4.3: Generate Google App Password

1. Open https://myaccount.google.com/apppasswords

2. Login again if asked

3. Select:

 	App: Mail

	Device: Other (Custom)

4. Enter name:

```
Laravel 11 Mail App

```

5. Click Generate

### Google will generate a 16-digit password like:

```
abcd efgh ijkl mnop

```

<img width="1919" height="912" alt="Screenshot 2026-01-29 162424" src="https://github.com/user-attachments/assets/3f4097ac-7d1a-4fe2-8b0c-ee72a10e2b29" />

Important:

Copy this password

Do NOT share it

This is your MAIL_PASSWORD


### Open .env file and update mail configuration:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=your_16_digit_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME="Laravel11 Mail App"


```

### Step 4.4: Clear Configuration Cache

```
php artisan config:clear
php artisan cache:clear

```



Step 5: Create Model, Controller & Mailable
```
php artisan make:model MailLog
php artisan make:controller MailController
php artisan make:mail TestMail

```
MailLog Model (app/Models/MailLog.php)
```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email','subject','message','created_by','updated_by','status'
    ];
}
```
MailController (app/Http/Controllers/MailController.php)
```

<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\MailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Display the email sending form.
     */
   public function index()
    {
        return view('mail.form');
    }

    /**
     * Handle the email sending process.
     */
    public function send(Request $request)
    {
        // 1. Validation
        $request->validate([
            'email'         => 'required|email',
            'subject'       => 'required|string|max:255',
            'message'       => 'required',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120'
        ]);

        try {
            // 2. Process attachments
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Files are stored in storage/app/public/attachments
                    $attachmentPaths[] = $file->store('attachments', 'public');
                }
            }

            // 3. Prepare data for the email
            $details = [
                'title'       => $request->subject,
                'body'        => $request->message,
                'attachments' => $attachmentPaths,
            ];

            // 4. Create database log
            MailLog::create([
                'email'      => $request->email,
                'subject'    => $request->subject,
                'message'    => $request->message,
                'created_by' => 1,
                'status'     => 1
            ]);

            // 5. Attempt to send the email
            Mail::to($request->email)->send(new TestMail($details));

            // If successful, return success view
            return view('mail.success');

        } catch (Exception $e) {
            // IF MAIL FAILS, IT WILL SHOW THE REAL ERROR HERE
            return "Mail Sending Failed! Error: " . $e->getMessage();
        }
    }

    /**
     * List all sent mail logs with search functionality.
     */
    public function list(Request $request)
    {
        $query = MailLog::where('status', 1);

        // Filter logs based on search query (email or subject)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        // Paginate results (10 per page)
        $mails = $query->orderBy('id', 'ASC')->paginate(10);

        return view('mail.index', compact('mails'));
    }

    /**
     * View details of a single mail log entry.
     */
    public function view($id)
    {
        $mail = MailLog::findOrFail($id);
        return view('mail.view', compact('mail'));
    }

    /**
     * Soft delete a mail log.
     */
    public function delete($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->delete();

        return redirect()->back()->with('success', 'Mail deleted successfully!');
    }

    /**
     * Restore a soft-deleted mail log.
     */
    public function restore($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->restore();
        return redirect()->back()->with('success', 'Email restored successfully!');
    }

    /**
     * Permanently delete a mail log entry.
     */
    public function forceDelete($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->forceDelete();
        return redirect()->back()->with('success', 'Email permanently deleted!');
    }

    /**
     * Toggle the status of a mail log (Active/Inactive).
     */
    public function changeStatus($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();

        return redirect()->back()->with('success', 'Status updated!');
    }
}
```
TestMail Mailable (app/Mail/TestMail.php)
```

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Ensure MAIL_FROM_ADDRESS is set in your .env
        $mail = $this->from(config('mail.from.address'), config('mail.from.name'))
                     ->subject($this->details['title'])
                     ->view('emails.test')
                     ->with('details', $this->details);

        // Attach multiple files if available
        if (isset($this->details['attachments']) && is_array($this->details['attachments'])) {
            foreach ($this->details['attachments'] as $filePath) {
                $fullPath = storage_path('app/public/' . $filePath);
                
                if (file_exists($fullPath)) {
                    $mail->attach($fullPath);
                }
            }
        }

        return $mail;
    }
}
```
Step 6: Routes (routes/web.php)
```

use App\Http\Controllers\MailController;

Route::get('/email', [MailController::class,'index']);
Route::post('/send-email', [MailController::class,'send']);

Route::get('/mail', [MailController::class,'list']);
Route::get('/mail/view/{id}', [MailController::class,'view']);
Route::get('/mail/delete/{id}', [MailController::class,'delete']);
Route::get('/mail/restore/{id}', [MailController::class,'restore']);
Route::get('/mail/force-delete/{id}', [MailController::class,'forceDelete']);
Route::get('/mail/status/{id}', [MailController::class,'changeStatus']);
```
Step 7: Blade Views
Layout (resources/views/layouts/app.blade.php)
```

<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark w-100 shadow-sm text-center">
    <span class="navbar-brand mb-0 h1">Mail Sender</span>
</nav>
<div class="container">@yield('content')</div>
</body>
</html>
```
Send Form (resources/views/mails/form.blade.php)
```

@extends('layouts.app')

@section('content')
<br>
<div class="row justify-content-center">
    <div class="col-md-6">

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Send Email</h4>
            </div>

            <div class="card-body">
                <form action="/send-email" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" id="editor" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Attachments (PDF/Image)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Send Email</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

<script>
    // Initialize CKEditor on the element with id "editor"
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>

@endsection
```
Email Logs (resources/views/mails/index.blade.php)
```

@extends('layouts.app')
@section('content')
<div class="card shadow-lg">
<div class="card-header bg-primary text-white d-flex justify-content-between">
<h4>Mail Logs</h4>
<a href="/email" class="btn btn-success btn-sm">Send Email</a>
</div>
<div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered table-striped">
<thead class="table-dark"><tr><th>ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Created At</th><th>Action</th></tr></thead>
<tbody>
@forelse($mails as $mail)
<tr>
<td>{{ $mail->id }}</td>
<td>{{ $mail->email }}</td>
<td>{{ $mail->subject }}</td>
<td>@if($mail->status==1)<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Inactive</span>@endif</td>
<td>{{ $mail->created_at->format('d-m-Y') }}</td>
<td>
<a href="/mail/view/{{ $mail->id }}" class="btn btn-sm btn-primary">View</a>
<a href="/mail/delete/{{ $mail->id }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
</td>
</tr>
@empty
<tr><td colspan="6" class="text-center">No Email Logs Found</td></tr>
@endforelse
</tbody>
</table>
<div class="d-flex justify-content-center mt-3">{{ $mails->links('pagination::bootstrap-5') }}</div>
</div>
</div>
@endsection
```
View Single Email (resources/views/mails/view.blade.php)
```

@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card border-0 shadow-lg rounded-4">

                <div class="card-header text-white text-center py-4 rounded-top-4" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
                    <h3 class="mb-0"><i class="bi bi-envelope-fill me-2"></i>Email Details</h3>
                </div>

                <div class="card-body p-4">

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Recipient Email</h6>
                        <div class="p-3 border rounded bg-light text-break">{{ $mail->email }}</div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Subject</h6>
                        <div class="p-3 border rounded bg-light text-break">{{ $mail->subject }}</div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold">Message</h6>
                        <div class="p-3 border rounded bg-light">
                            {!! $mail->message !!}
                        </div>
                    </div>

                    <div class="row mb-4 g-3 text-center">
                        <div class="col-md-6">
                            <div class="p-3 border rounded shadow-sm bg-white">
                                <h6 class="text-muted fw-bold mb-2"><i class="bi bi-toggle-on me-2"></i>Status</h6>
                                @if($mail->status == 1)
                                    <span class="badge bg-success px-3 py-2 fs-6">Active</span>
                                @else
                                    <span class="badge bg-danger px-3 py-2 fs-6">Inactive</span>
                                @endif

                                @if($mail->deleted_at)
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6 ms-1">Deleted</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded shadow-sm bg-white">
                                <h6 class="text-muted fw-bold mb-2"><i class="bi bi-calendar-check me-2"></i>Created At</h6>
                                <div>{{ $mail->created_at->format('d-m-Y H:i A') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center flex-wrap gap-3">
                        <a href="{{ url('/mail') }}" class="btn btn-secondary btn-lg shadow-sm">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endsection
```
Success Page (resources/views/mails/success.blade.php)
```

@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-lg">
<div class="card-header bg-success text-white"><h4>Success!</h4></div>
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
```
Email Template (resources/views/emails/test.blade.php)
```

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
        
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        .email-header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .email-body {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }

        .email-footer {
            background-color: #f1f1f1;
            color: #555555;
            text-align: center;
            padding: 15px;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

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
                    <tr>
                        <td class="email-header">
                            <h1>{{ $details['title'] }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-body">
                            {!! $details['body'] !!}
                        </td>
                    </tr>

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
```
Step 8: Run the Application
bash
```
php artisan serve
```
Visit in browser:
```
Email Form: http://localhost:8000/email

Mail Logs: http://localhost:8000/mail
```

You can see this type Output :

Email Form: http://localhost:8000/email :
<img width="1913" height="959" alt="image" src="https://github.com/user-attachments/assets/7c228df4-5957-46ef-8404-72ccc6b37373" />
<img width="1917" height="972" alt="Screenshot 2025-12-04 113812" src="https://github.com/user-attachments/assets/addf6504-0c4d-49a4-91d0-b0076920b4c8" />

Mail Logs: http://localhost:8000/mail :
<img width="1919" height="968" alt="image" src="https://github.com/user-attachments/assets/6d65959c-8dbb-44fb-905b-ae608a86c314" />
<img width="1894" height="964" alt="Screenshot 2025-12-04 114747" src="https://github.com/user-attachments/assets/71b00090-9125-4f86-9a36-970a5b3ce56e" />


 Features Now Working
```
Send emails via form

View email logs with pagination

Soft delete, restore, and permanently delete emails

Toggle status (Active/Inactive)

Mobile responsive with Bootstrap 5
```
