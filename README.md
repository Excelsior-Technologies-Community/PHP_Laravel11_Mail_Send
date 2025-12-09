PHP_Laravel12_Mail_Send
---
By: Manasi Patel / Laravel11-Mail
Date: 2025
Laravel Version: 11

This project demonstrates a Mail Send System using Laravel 11. Users can send emails via a form, view email logs, and manage them with soft delete and restore functionality.

Everything is fully commented and explained step-by-step for beginners.

Features
---
Send emails using a simple form

Save email logs in the database

View all email logs with pagination

Soft delete, restore, and permanently delete email logs

Toggle active/inactive status for emails

Responsive Bootstrap 5 design

Installation & Setup
---
1. Install Laravel 11
```
composer create-project laravel/laravel PHP_Laravel12_Mail_Send "^11.0"
cd PHP_Laravel12_Mail_Send
```
3. Configure Database

Update your .env file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mail_app
DB_USERNAME=root
DB_PASSWORD=
```

Create the database:

CREATE DATABASE mail_app;

3. Create Migration for mail_logs Table
 ```
php artisan make:migration create_mail_logs_table --create=mail_logs
```

Edit the migration file database/migrations/xxxx_create_mail_logs_table.php:
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
            $table->id(); // Primary key
            $table->string('email'); // Recipient email
            $table->string('subject'); // Email subject
            $table->longText('message'); // Full email message
            $table->unsignedBigInteger('created_by')->nullable(); // Creator ID
            $table->unsignedBigInteger('updated_by')->nullable(); // Updater ID
            $table->softDeletes(); // Soft delete column
            $table->tinyInteger('status')->default(1); // 1=Active, 0=Inactive
            $table->timestamps(); // created_at & updated_at
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
4. Configure Mail Settings

In .env, configure SMTP or Mailtrap:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="from@example.com"
MAIL_FROM_NAME="Laravel11-Mail"
```
5. Create Model & Controller
```
php artisan make:model MailLog -m
php artisan make:controller MailController --resource --model=MailLog
```
MailLog Model (app/Models/MailLog.php)
```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes; // Enable soft delete

    protected $fillable = [
        'email', 'subject', 'message', 'created_by', 'updated_by', 'status'
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
    // Show mail form
    public function index()
    {
        return view('mails.form');
    }

    // Send email and save log
    public function send(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'subject' => 'required',
            'message' => 'required',
        ]);

        // Save log to DB
        $log = MailLog::create([
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'created_by' => 1, // Replace with Auth::id()
            'status' => 1,
        ]);

        $details = ['title' => $request->subject, 'body' => $request->message];

        // Send mail
        Mail::to($request->email)->send(new TestMail($details));

        return view('mails.success');
    }

    // List all mails
    public function list()
    {
        $mails = MailLog::where('status',1)->orderBy('id','ASC')->paginate(10);
        return view('mails.index', compact('mails'));
    }

    // View single mail
    public function view($id)
    {
        $mail = MailLog::findOrFail($id);
        return view('mails.view', compact('mail'));
    }

    // Soft delete
    public function delete($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->delete();
        return redirect()->back()->with('success','Mail deleted successfully!');
    }

    // Restore soft deleted mail
    public function restore($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->restore();
        return redirect()->back()->with('success','Mail restored!');
    }

    // Force delete permanently
    public function forceDelete($id)
    {
        $mail = MailLog::withTrashed()->findOrFail($id);
        $mail->forceDelete();
        return redirect()->back()->with('success','Mail permanently deleted!');
    }

    // Change status Active/Inactive
    public function changeStatus($id)
    {
        $mail = MailLog::findOrFail($id);
        $mail->status = $mail->status == 1 ? 0 : 1;
        $mail->save();
        return redirect()->back()->with('success','Status updated!');
    }
}
```
6. Define Routes (routes/web.php)
```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

// Home
Route::get('/', fn() => view('welcome'));

// Mail Routes
Route::get('/email', [MailController::class,'index']);
Route::post('/send-email', [MailController::class,'send']);

// Admin mail logs
Route::get('/mail', [MailController::class,'list']);
Route::get('/mail/view/{id}', [MailController::class,'view']);
Route::get('/mail/delete/{id}', [MailController::class,'delete']);
Route::get('/mail/restore/{id}', [MailController::class,'restore']);
Route::get('/mail/force-delete/{id}', [MailController::class,'forceDelete']);
Route::get('/mail/status/{id}', [MailController::class,'changeStatus']);
```

7. Create Blade Views
Layout (resources/views/layouts/app.blade.php)
```
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark w-100">
    <div class="container-fluid text-center">
        <span class="navbar-brand mb-0 h1">Mail Sender</span>
    </div>
</nav>
<div class="container mt-4">@yield('content')</div>
</body>
</html>
```
Mail Form (resources/views/mails/form.blade.php)
```
@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-lg">
      <div class="card-header bg-primary text-white"><h4>Send Email</h4></div>
      <div class="card-body">
        <form action="/send-email" method="POST">@csrf
          <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label>Subject</label><input type="text" name="subject" class="form-control" required></div>
          <div class="mb-3"><label>Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
          <button type="submit" class="btn btn-success w-100">Send Email</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
```
Mail Logs (resources/views/mails/index.blade.php)
```
@extends('layouts.app')
@section('content')
<div class="card shadow-lg">
  <div class="card-header bg-primary text-white d-flex justify-content-between">
    <h4>Mail Logs</h4>
    <a href="{{ url('/email') }}" class="btn btn-success btn-sm">Send Email</a>
  </div>
  <div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Created At</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($mails as $mail)
        <tr>
          <td>{{ $mail->id }}</td>
          <td>{{ $mail->email }}</td>
          <td>{{ $mail->subject }}</td>
          <td>@if($mail->status==1)<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Inactive</span>@endif</td>
          <td>{{ $mail->created_at->format('d-m-Y') }}</td>
          <td>
            <a href="{{ url('/mail/view/'.$mail->id) }}" class="btn btn-sm btn-primary">View</a>
            <a href="{{ url('/mail/delete/'.$mail->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete?');">Delete</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">No Email Logs Found</td></tr>
        @endforelse
      </tbody>
    </table>
    <div class="d-flex justify-content-center mt-3">{{ $mails->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
  </div>
</div>
@endsection
```
Mail View (resources/views/mails/view.blade.php)
```
@extends('layouts.app')
@section('content')
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
      <div class="card shadow-lg rounded-4">
        <div class="card-header text-white text-center py-4 rounded-top-4" style="background: linear-gradient(90deg, #4e73df, #1cc88a);">
          <h3>Email Details</h3>
        </div>
        <div class="card-body p-4">
          <div class="mb-4"><strong>Email:</strong> {{ $mail->email }}</div>
          <div class="mb-4"><strong>Subject:</strong> {{ $mail->subject }}</div>
          <div class="mb-4"><strong>Message:</strong> <div style="white-space: pre-wrap;">{{ $mail->message }}</div></div>
          <a href="{{ url('/mail') }}" class="btn btn-secondary">Back</a>
        </div>
      </div>
    </div>
  </div>
</div>
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
        <p>Your message was delivered.</p>
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
body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:0; }
.email-container { max-width:600px; margin:40px auto; background:#fff; border-radius:8px; overflow:hidden; }
.email-header { background:#007bff; color:#fff; padding:20px; text-align:center; }
.email-body { padding:20px; color:#333; line-height:1.6; }
.email-footer { background:#f1f1f1; color:#555; text-align:center; padding:15px; font-size:12px; }
</style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td>
  <table class="email-container" cellpadding="0" cellspacing="0">
    <tr><td class="email-header"><h1>{{ $details['title'] }}</h1></td></tr>
    <tr><td class="email-body"><p>{{ $details['body'] }}</p></td></tr>
    <tr><td class="email-footer">&copy; {{ date('Y') }} Your Company. All rights reserved.</td></tr>
  </table>
</td></tr>
</table>
</body>
</html>
```
8. Create Mailable
```
php artisan make:mail TestMail
```

app/Mail/TestMail.php:
```
<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public $details;
    public function __construct($details) { $this->details = $details; }
    public function build() { return $this->subject($this->details['title'])->view('emails.test'); }
}
```
9. Run the Application
```
php artisan serve

```
Open browser:
```
http://localhost:8000/email
```

Now you can:
```
Send emails via a form

View email logs

Soft delete, restore, or permanently delete emails

Paginate logs and toggle status
```
✅ Congratulations! Your Laravel 11 Mail Send Project is fully functional.
